<?php

  class Request {
    protected static $instance;
    protected $method;
    protected $pathInfo;
    protected $parameters; // query
    protected $format;
    protected $params;
    protected $body;
    protected $headersData;
    protected $headersMap;

    static function getInstance() {
      return self::$instance;
    }

    function headers($header=null) {
      if (!isset($header)) {
        return $this->headersData;
      }
      if (!is_string($header) || !is_array($this->headersMap)) {
        return false;
      }
      $index = strtolower($header);
      if (!array_key_exists($index, $this->headersMap)) {
        return false;
      }
      return $this->headersData[$this->headersMap[$index]];
    }

    function getMethod() {
      return $this->method;
    }

    function getPathInfo() {
      return $this->pathInfo;
    }


/**
    * Parse out url query string into an associative array
    *
    * $qry can be any valid url or just the query string portion.
    * Will return false if no valid querystring found
    *
    * @ param $qry String
    * @ return Array
    */
    static function queryToArray($qry) {
      $result = array();
      //string must contain at least one = and cannot be in first position
      if(strpos($qry,'=')) {

        if(strpos($qry,'?')!==false) {
          $q = parse_url($qry);
          $qry = $q['query'];
        }
      }else {
        return false;
      }

      foreach (explode('&', $qry) as $couple) {
        list ($key, $val) = explode('=', $couple);
        $result[$key] = $val;
      }

      return empty($result) ? false : $result;
    }

    function body($key=null) {
      $contentType = "";
      if (isset($this->headersMap["content-type"]) && isset($this->headersData[$this->headersMap["content-type"]])) {
        $contentType = strtolower(trim(explode(";",$this->headersData[$this->headersMap["content-type"]])[0]));
      }
      //if ($this->format==='json') {
      switch ($contentType) {
        case 'multipart/form-data':
          $body = $_POST;
        break;
        
        case 'application/json':
          $body = json_decode(file_get_contents('php://input'), true);
        break;
        
        case 'application/x-www-form-urlencoded':
          $body = self::queryToArray(file_get_contents('php://input'));
        break;

        default: // 'application/json'
          $body = file_get_contents('php://input')  ;
          //Response::getInstance()->send($this->headersData);
          //Response::getInstance()->send($body);
        break;
      }
      if (is_string($key)) {
        //return filter_input(INPUT_POST, $key);
        //return filter_var($body[$key]);
        return isset($body[$key]) ? $body[$key] : null;
      }
      return $body;
    }

    function getUrlElements($index=null) {
      $elements = explode("/", rtrim($this->pathInfo,"/"));
      if (isset($index)) {
        if (!isset($elements[$index])) {
          return null;
        }
        return $elements[$index];
      }
      return $elements;
    }

    function getParameters() {
      return $this->parameters;
    }

    function params($key=null) {
      if (isset($key)) {
        return isset($this->params[$key]) ? $this->params[$key] : null;
      }
      return $this->params;
    }

    function getFormat() {
      return $this->format;
    }

    function param($key) {
      if (!array_key_exists($key, $this->params)) {
        return null;
      }
      return $this->params[$key];
    }

    function __construct() {
      $this->method = $_SERVER['REQUEST_METHOD'];

      $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "/";
      $pathInfo = str_replace("//", "/", $pathInfo);
      if ($pathInfo!=="/") {
        $pathInfo = rtrim($pathInfo, "/");
      }
      $this->pathInfo = $pathInfo;
    
      $this->parseIncomingParams();

      // initialise json as default format
      $this->format = 'json';
      if(isset($this->parameters['format'])) {
        $this->format = $this->parameters['format'];
      }

      //if (is_array($_POST) && count($_POST)>0) {
      //  $this->body = array();
      //  foreach($_POST as $key => $value) {
      //    $this->body[$key] = filter_input(INPUT_POST, $key);
      //  }
      //}

      $this->headersData = apache_request_headers();
      if (is_array($this->headersData)) {
        $keys = array_keys($this->headersData);
        $map = array();
        foreach ($keys as $key) {
          $map[strtolower($key)] = $key;
        }
        $this->headersMap = $map;
      }

      $data = json_decode(file_get_contents('php://input'), true);

      return true;
    }

    function parseIncomingParams() {
      $parameters = array();

      // first of all, pull the GET vars
      if (isset($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $parameters);
      }

      // now how about PUT/POST bodies? These override that we got from GET
      $body = file_get_contents("php://input");
      $content_type = false;
      if(isset($_SERVER['CONTENT_TYPE'])) {
        $content_type = $_SERVER['CONTENT_TYPE'];
      }
      switch($content_type) {
        case "application/json":
          $body_params = json_decode($body);
          if (is_array($body_params)) {
            foreach ($body_params as $param_name => $param_value) {
              $parameters[$param_name] = $param_value;
            }
          }
          $this->format = "json";
          break;

        case "application/x-www-form-urlencoded":
          parse_str($body, $postvars);
          if (is_array($body)) {
            foreach ($postvars as $field => $value) {
              $parameters[$field] = $value;
            }
          }
          $this->format = "html";
          break;

        default:
          // we could parse other supported formats here
          break;
      }
      $this->parameters = $parameters;
    }

    function verifyRoute($route) {
      //return getPathInfo()!==$route
      $routeElements = explode("/", rtrim($route,"/"));
      $urlElements = $this->getUrlElements();
      
      echo "<h1>verifyRoute.route: $route</h1>";
      echo "<h1>verifyRoute.routeElements:</h1>";
      var_dump($routeElements);
      //
      //echo "<h1>verifyRoute.urlElements: ".implode("/",$urlElements)."</h1>";
      echo "<h1>verifyRoute.urlElements:</h1>";
      var_dump($urlElements);

      if (count($routeElements)!==count($urlElements)) {
        echo "<p>quantia de elements diferem</p>";
        return false; // quantia de elements diferem 
      }

      foreach ($routeElements as $index => $routeElement) {
        if (!array_key_exists($index, $urlElements)) { // element não existe
          echo "<p>element não existe</p>";
          return false;
        }
        
        $urlElement = $urlElements[$index];
        
        if (substr($routeElement,0,1)===":") { // param encontrado
          echo "<p>param encontrado</p>";
          continue;
        }
        
        if ($routeElement!==$urlElement) { // elements não correspondem
          echo "<p>elements não correspondem</p>";
          return false;
        }
      }

      return true;
    }

    function parseParams($route) {
      echo "<h1>parseParams.route: $route</h1>";
      $params = array();
      if (is_string($route)) {
        $elements = explode("/", $route);
        $urlElements = $this->getUrlElements();
        foreach ($elements as $index => $element) {
          if (substr($element,0,1)===":") {
            $key = ltrim($element, ":");
            $value = isset($urlElements[$index]) ? $urlElements[$index] : null;
            $params[$key] = $value;
          } 
        }
      }
      $this->params = $params;
      if (count($params)>0) {
        echo "<h1>Request->params:</h1>";
        var_dump($params);
        //exit();
      }
    }
  
    static function init() {
      self::$instance = new Request();
    }
  }
  Request::init();
