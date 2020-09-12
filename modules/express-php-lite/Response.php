<?php

  class Response {
    protected static $instance;
    protected $data;
    protected $statusCode;
    protected $format;
    protected $result;
    protected $headersSent = false;
    protected $jsonCleanOb = true;
    protected $echo = true;
    protected $exit = true;

    static function getInstance() {
      return self::$instance;
    }

    static function init() {
      self::$instance = new Response();
    } 

    function status($status) {
      $this->statusCode = $status;
      header("HTTP/1.1 " . $status . " " . self::statusText($status));
      return $this;
    }

    function headersSent() {
      return $this->headersSent;
    }

    function isJsonCleanOb($enable=null) {
      if ($enable!==null) {
        $this->jsonClean = $enable==true;
      }
      return $this->jsonClean;
    }

    function isEcho($enable=null) {
      if ($enable!==null) {
        $this->echo = $enable==true;
      }
      return $this->echo;
    }

    function isExit($enable=null) {
      if ($enable!==null) {
        $this->exit = $enable==true;
      }
      return $this->exit;
    }

    function set($field, $content=null) {
      if (is_string($field) && is_string($content)) {
        header("$field: $content");
      }
      else if (is_array($field)) {
        foreach ($field as $index => $element) {
          $this->set($index, $element);
        }
      }
    }

    function send($data=null) {
      $this->data = $data;
      if (!$this->statusCode) {
        $this->status(200);
      }
      if (!$this->format) {
        $this->format = is_string($data) || is_null($data) ? 'text' : 'json';
      }
      if ($this->format==='text') {
        $result = is_string($data) || is_null($data) ? $data : json_encode($data);
        $this->set('Content-Type', 'text/html; charset=utf-8');
      }
      else {
        $result = is_null($data) ? "" : json_encode($data);
        if ($this->jsonCleanOb 
        && !is_null($data)
        ) {
          ob_get_clean();
        }
        $this->set('Content-Type', 'application/json; charset=utf-8');
      }
      $this->result = $result;
      $this->headersSent = true;
      if ($this->echo) {
        echo $result;
      }
      if ($this->exit && !is_null($data)) {
        exit();
      }
      return $this;
    }
    
    function json($data=null) {
      $this->format = "json";
      $this->send($data);
    }

    function end() {
      $this->isJsonCleanOb(false);
      $this->isExit(true);
      $this->send();
    }

    static function statusText($code) {
      $status = array(  
        200 => 'OK',
        400 => 'Bad Request',   
        401 => 'Unauthorized',   
        404 => 'Not Found',   
        410 => 'Gone',   
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
      ); 
      return (isset($status[$code]))?$status[$code]:$status[500]; 
    }
  }
  Response::init();
