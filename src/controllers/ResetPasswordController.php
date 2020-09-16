<?php

class ResetPasswordController {

  function index($req, $res) {
    return $res->send("<h1>Olá ResetPassword index!</h1>");
  }

  function update($req, $res) {
    $auth = new Auth(RESET_JWT_SECRET);
    if (!$auth->execute($req,$res)) {
      return false;
    }

    $data = $req->body();
    $newPassword = isset($data['newPassword']) ? $data['newPassword'] : false;
    $confirmPassword = isset($data['confirmPassword']) ? $data['confirmPassword'] : false;
    if (!$newPassword || !$confirmPassword || $newPassword!==$confirmPassword) {
      return $res->status(400)->send(['error'=>'Password error.']);
    }
    
    $model = new Admin();
    $id = $req->userId;
    $found = $model->findByPk($id);
    if (!$found) {
      return $res->status(400)->send(['error'=>'User not found.']);
    }

    $update = $model->update([
      'hash' => password_hash($newPassword, PASSWORD_BCRYPT),
    ]);
    if (!$update) {
      return $res->status(500)->send(['error'=>'Error updating password.']);
    }

    return $res->send(['message'=>'Password has been updated!']);
  }
  
}