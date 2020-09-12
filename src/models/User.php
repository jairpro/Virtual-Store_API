<?php

class User extends MyModel {

  function find($idOrUserOrEmail) {
    $options = array(
      "id" => $idOrUserOrEmail,
      "user" => $idOrUserOrEmail,
      "email" => $idOrUserOrEmail
    );
    return $this->findOne($options);
  }
}
