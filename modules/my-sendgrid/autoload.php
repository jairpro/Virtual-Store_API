<?php
  spl_autoload_register('mySendgridAutoload');

  function mySendgridAutoload($classname) {
    $dir = dirname(__FILE__)."/";

    $php = $dir . "/$classname.php";
    if (file_exists($php)) {
      require_once $php;
      return true;
    }

    return false;
  }
