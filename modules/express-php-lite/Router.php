<?php
  class Router {

    protected static $instance;
    protected $request;
    protected $response;

    function __construct() {
      $this->request = Request::getInstance();
      $this->response = Response::getInstance();
    }

    function emit($controller, $action) {
      $request = Request::getInstance();
      $response = Response::getInstance();
      $controller->$action($request, $response);
    }
    
    static function getInstance() {
      return self::$instance;
    }

    static function init() {
      self::$instance = new Router();
    }

    function use($action) {
      $req = $this->request;
      $res = $this->response;
      
      if (is_array($action)) {
        $className = isset($action[0]) ? $action[0] : false;
        if (!class_exists($className)) {
          return $res->status(501)->send(['error'=> "Class $className not implemented."]);
        }
        $method_name = isset($action[1]) ? $action[1] : false;
        if (!method_exists($className, $method_name)) {
          return $res->status(501)->send(['error'=> "Method $className.$method_name not implemented."]);
        }
        $obj = new $className();
        $obj->$method_name($req, $res);
      }
      else if (is_callable($action)) {
        $action($req, $res);
      }
    }

    function get($route, $action) {
      $this->proccess('GET', $route, $action);
    }

    function put($route, $action) {
      $this->proccess('PUT', $route, $action);
    }

    function post($route, $action) {
      $this->proccess('POST', $route, $action);
    }

    function delete($route, $action) {
      $this->proccess('DELETE', $route, $action);
    }

    function proccess($method, $route, $action) {

      $response = Response::getInstance();

      if ($response->headersSent()) {
        return false;
      }

      $request = Request::getInstance();
      if ($request->getMethod()!==$method) {
        return false;
      }

      $debug = false;
      if (!$debug) {
        ob_start();
      }
      $routeOk = $request->verifyRoute($route);
      if (!$debug) {
        ob_end_clean();
      }
      if (!$routeOk) {
        return false;
      }

      if (!$debug) {
        ob_start();
      }
      $request->parseParams($route);
      if (!$debug) {
        ob_end_clean();
      }

      $this->request = $request;
      $this->response = $response;
      $this->use($action);
    }
  }
  Router::init();