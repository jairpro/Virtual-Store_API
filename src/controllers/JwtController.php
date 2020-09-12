<?php

  class JwtController {

    function enabled() {
      return JWT_TESTS == true;
    }

    function validate($req, $res) {
      if (!$this->enabled()) {
        return null;
      }
      $jwt = new MyJWT(); 
      if (!$jwt->validate(
        $req->body('token'),
        $req->body('key') ? $req->body('key') : "secret"
      )) {
        return $res->status(400)->send($jwt->getResult());
      }
      return $res->send($jwt->getResult());
    }

    function generateKey($req, $res) {
      if (!$this->enabled()) {
        return null;
      }
      return $res->json(['key' => MyJWT::generateKey()]);
    }

    function generateToken($req, $res) {
      if (!$this->enabled()) {
        return null;
      }
      return $res->json(['token' => MyJWT::getInstance()->generateToken(
        $req->body('data'), 
        $req->body('key') ? $req->body('key') : MyJWT::generateKey()
      )]);
    }

  }