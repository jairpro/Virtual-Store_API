<?php
    spl_autoload_register('apiAutoload');

    function apiAutoload($classname) {
      $dir = dirname(__FILE__)."/";
  
      $lista = array();
      $lista[] = $dir . "../modules/$classname.php";
      $lista[] = $dir . "../modules/my-model/$classname.php";
      $lista[] = $dir . "utils/$classname.php";
      $lista[] = $dir . "models/$classname.php";
      $lista[] = $dir . "controllers/$classname.php";
      $lista[] = $dir . "middlewares/$classname.php";
      $lista[] = $dir . "$classname.php";

      foreach ($lista as $index => $php) {
        if (file_exists($php)) {
          require_once $php;
        }
      }
    }