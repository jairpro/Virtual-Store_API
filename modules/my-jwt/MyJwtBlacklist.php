<?php

class MyJwtBlacklist extends MyJWT {

  protected $blacklistPath;

  function __construct() {
    parent::__construct();
    
    $this->blacklistPath = dirname(__FILE__)."/.blacklist";
  }

  function revoke($token) {
    $bytes = null;
    try {
      $bannedToken = password_hash($token, PASSWORD_BCRYPT);
      $content = $bannedToken.PHP_EOL;
      $bytes = file_put_contents($this->blacklistPath, $content, FILE_APPEND);
    }
    catch (Exception $e) {
      $this->message = $e->getMessage();
    }
    $success = $bytes===strlen($content);
    return $success;
  }

  function isRevoked($token) {
    try {
      $file = $this->blacklistPath;
      if (!file_exists($file)) {
        return false;
      }
      $list = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if (is_array($list)) {
        foreach ($list as $element) {
          if (password_verify($token, $element)) {
            return true;
          }
        }
      }
    }
    catch (Exception $e) {
      $this->message = $e->getMessage();
    }
    return null;
  }

  /*
  // Como os tokens são armazenados com hash ainda não é possível verificar a expiração
  // A solução pode ser armazenando no método revoke() 2 campos por linha (hash e MyJWT->result['payload']['exp'])
  //
  // Remove tokens expirados da blacklist
  function wipeBlacklist($secret=null) {
    try {
      $secret = isset($secret) ? $secret : MY_JWT_SECRET;
      $file = $this->blacklistPath;
      if (!file_exists($file)) {
        return null;
      }
      $list = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if (is_array($list)) {
        $index = 0;
        $removals = 0;
        while($index<count($list)) {
          $element = $list[$index];
          if ($this->validate($element)) {
            $index++;
          }
          else {
            array_splice($list, $index, 1);
            $removals++;
          }
        }
        $content = explode(PHP_EOL, $list);
        $bytes = file_put_contents($file, $content);
        $success = $bytes===strlen($content);
        return $success ? $removals : false;
      }
      return null;
    }
    catch (Exception $e) {
      $this->message = $e->getMessage();
      return false;
    }
  }
  */
  static function init() {
    static::$instance = new MyJwtBlacklist();
  }
}
MyJwtBlacklist::init();