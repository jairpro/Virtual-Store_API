<?php
  ini_set("error_log", "../php_errors.log");

  require_once "../.env.php";
  require_once "../modules/express-php-lite/autoload.php";
  require_once "../modules/my-jwt/autoload.php";
  require_once "../src/autoload.php";

  Debug::cleanNext();

  require_once "../src/routes.php";
