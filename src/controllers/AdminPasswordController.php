<?php

class AdminPasswordController {
  
  function update($req, $res) {
    if (!isset($req->userId)) {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    $model = new Admin();

    if (!$model->setup()) {
      $res->status(500)->send(['error'=>'Database connection failure.']);
    }

    $me = $model->findByPk($req->userId);

    if (!$me) {
      $res->status(500)->send(['error'=>'Invalid token or user not found.']);
    }
    
    if ($me['status']==='I') {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    $data = $req->body();
    if (!is_array($data) or empty($data)) {
      $res->status(400)->send(['error' => "Invalid request body."]);
    }

    $dataKeys= array_keys($data);

    $schema = [
      'password' => [
        'required' => true,
      ],
      'newPassword' =>[
        'required' => true,
        //'min' => 8,
        //'have' => ['number','lower','upper','letter','simbol']
      ],
      'confirmPassword' =>[
        'required' => true,
        'equal' => 'newPassword'
      ],
    ];

    // Se tentar alterar campo além do permitido
    foreach($schema as $field => $options) {
      if (!in_array($field, $dataKeys)) {
        return $res->status(400)->send(['error'=>"Missing the $field field."]);
      }
      if (isset($options['required']) && $options['required'] && !$data[$field]) {
        return $res->status(400)->send(['message'=>"The $field field cannot be empty."]);
      }
      if ($field==='password' && !password_verify($data['password'], $me['hash'])) {
        return $res->status(401)->send(['message'=>"password not match."]);
      }
      if ($field==='confirmPassword' && $data['newPassword']!==$data['confirmPassword']) {
        return $res->status(400)->send(['message'=>"the new password and confirmation must be the same."]);
      }
    }

    $newData = [
      'hash' => password_hash($data['newPassword'], PASSWORD_BCRYPT)
    ];

    $result = $model->update($newData);

    if (!$result) {
      $res->status(500)->send(['error'=>"Update password failure."]);
    }

    $res->send(['message'=>'The password was successfully changed.']);
  }

}