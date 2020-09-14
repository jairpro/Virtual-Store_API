<?php

class User extends MyModel {

  function find($idOrUserOrEmail) {
    $options = [
      'where'=>[
        'or'=>[
          "id" => $idOrUserOrEmail,
          "user" => $idOrUserOrEmail,
          "email" => $idOrUserOrEmail
        ]
      ]
    ];
    return $this->findOne($options);
  }
}
