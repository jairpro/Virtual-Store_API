<?php

class HomeController {

  function index($req, $res) {
    $res->send("<h1>Olá Virtual Store!</h1>");
  }

}