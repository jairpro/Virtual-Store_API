<?php

class SessionAdminController {
  
  function store($req=null, $res=null) {
    $admin = new Admin();

    $userName = $req->body('username');
    if (!$userName) {
      $res->status(400)->send(['message'=>"Mission username."]);
    }

    $password = $req->body('password');
    if (!$password) {
      $res->status(400)->send(['message'=>"Mission password."]);
    }

    if (!$admin->setup()) {
      $res->status(500)->send(['message'=>'Database connection failure.']);
    }

    $found = $admin->find($userName);
    
    if (!$found) {
      $res->status(400)->send(['message'=>"Invalid user."]);
    }
    
    if ($found['status']==='I') {
      $res->status(401)->send(['message'=>'Access denied.']);
    }
    
    $hash = $found['hash'];
    
    if (!password_verify($password, $hash)) {
      $res->status(401)->send(['message'=>"Password not match."]);
    }

    //$name = $found['name'];
    $id = $found['id'];
    //$res->send("Admin Login: olá $name");
    $res->send([
      'user' => [
        'id'=>$found['id'],
        'name'=>$found['name'],
        'email'=>$found['email'],
        'type'=>$found['type'],
        //'status'=>$found['status'],
      ],
      'token'=> MyJWT::getInstance()->generateToken([
        'id' => $id,
        //'type' => $found['type'],
        //'status' => $found['status']
      ])
    ]);
  }
}