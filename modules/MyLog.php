<?php
  class MyLog {
    protected static $file = '../my.log';
    protected static $mode = 0775;
    protected static $recursive = false;
    protected static $error;
    protected static $cleanNext = false;
    
    static function cleanNext() {
      self::$cleanNext = true;
    }

    static function file($file=null) {
      $result = static::$file;
      if (isset($file)) {
        static::$file = $file;
      }
      return $result;
    }

    static function getError() {
      return static::$error;
    }

    static function log($data, $file=null) {
      $dataStr = is_string($data) ? $data : json_encode($data) .PHP_EOL;
      $ok = false;
      try {
        $f = !isset($file) ? static::$file : $file;
        $dir = dirname($f);
        if (!file_exists($dir)) {
          mkdir($dir, static::$mode, static::$recursive);
        }
        $flags = !self::$cleanNext ? FILE_APPEND : null;
        $bytes = file_put_contents($f, $dataStr, $flags);
        $ok = $bytes===strlen($dataStr);
        if ($ok) {
          self::$cleanNext = false;
        }
        static::$error = '';
      }
      catch (Exception $e) {
        static::$error = ''; 
      }
      return $ok;
    }
  }
