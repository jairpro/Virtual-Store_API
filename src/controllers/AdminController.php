<?php

class AdminController {
  
  function index($req=null, $res=null) {
    if (!isset($req->userId)) {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    $admin = new Admin();
    
    if (!$admin->setup()) {
      $res->status(500)->send(['message'=>'Database connection failure.']);
    }

    $found = $admin->findByPk($req->userId);
    if (!$found) {
      $res->status(500)->send(['message'=>'User not found.']);
    }
    
    if ($found['status']==='I') {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    if (!in_array($found['type'], [
      Admin::TYPE_DEV, 
      Admin::TYPE_ADMIN
    ])) {
      $res->status(401)->send(['message'=>'You do not have permission.']);
    }

    $all = $admin->findAll([
      //'attributes' => 'id,user,name,email,status,type,created_at,updated_at'
      'attributes' => ['id','user','name','email','status','type','created_at','updated_at']
    ]);
    if (!$all) {
      $res->status(500)->send(['message'=>'Request failure.']);
    }

    $res->send($all);
  }

  function store($req=null, $res=null) {
    if (!isset($req->userId)) {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    $admin = new Admin();

    if (!$admin->setup()) {
      $res->status(500)->send(['message'=>'Database connection failure.']);
    }
    
    $found = $admin->findByPk($req->userId);
    if (!$found) {
      $res->status(500)->send(['message'=>'User not found.']);
    }
    
    if ($found['status']==='I') {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    if (!in_array($found['type'], [
      Admin::TYPE_DEV, 
      Admin::TYPE_ADMIN
    ])) {
      $res->status(401)->send(['message'=>'You do not have permission.']);
    }
    
    $data = $req->body();
    
    if (!isset($data['email']) || !$data['email']) {
      $res->status(402)->send(['message'=>'Missin email.']);
    }

    $password = isset($data['password']) ? $data['password'] : false;
    if (!$password) {
      $res->status(402)->send(['message'=>'Missin password.']);
    }
    unset($data['password']);
    $data['hash'] = password_hash($password, PASSWORD_BCRYPT);

    $result = $admin->create($data);
    
    if (!$result) {
      $res->status(500)->send(['message'=>'Operation failure.']);
    }

    $res->send($result);
  }

  function update($req=null, $res=null) {
    if (!isset($req->userId)) {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    $model = new Admin();

    if (!$model->setup()) {
      $res->status(500)->send(['message'=>'Database connection failure.']);
    }

    $me = $model->findByPk($req->userId);
    //Debug::log(['$me->id',$me->id]);

    if (!$me) {
      $res->status(500)->send(['message'=>'User not found.']);
    }
    
    if ($me['status']==='I') {
      $res->status(401)->send(['message'=>'Access denied.']);
    }

    $target = $model->findByPk($req->param('id'));
    if (!$target) {
      $res->status(404)->send(['message'=>'User not found.']);
    }

    /**
     *  Regra update por type:
     * 
     *  D: Desenv: acesso total
     *  A: Admin: acesso total exceto Desenv e Admin. em Admin permitido para si nos campos: email, name, user
     *  O: Operator: Em Operator permitido para si, nos campos: email, name, user; 
     * 
     * */
    $itsMe = $req->param('id')===$req->userId;
    $data = $req->body();
    if (!is_array($data) or empty($data)) {
      $res->status(400)->send(['message' => "Invalid request body."]);
    }

    $dataKeys= array_keys($data);

    switch ($me['type']) {
      case Admin::TYPE_DEV:
        $denyFields = [
          'id',
          'created_at',
          'updated_at',
        ];
        // alterando a si mesmo
        if ($itsMe) {
          $denyFields[] = 'type';
          $denyFields[] = 'status';
        }
        // se tentar alterar campo bloquado
        foreach($dataKeys as $key) {
          if (in_array($key, $denyFields)) {
            return $res->status(401)->send(['message'=>'You do not have permission.']);
          }
        }
      break;

      case Admin::TYPE_ADMIN:
        // alterando a si mesmo
        if ($itsMe) {
          // se tentando alterar campo além do permido
          $allowFields = [
            'email',
            'name',
            'user',
          ];
          // Se tentando alterar campo além do permido
          foreach($dataKeys as $key) {
            if (!in_array($key, $allowFields)) {
              return $res->status(401)->send(['message'=>'You do not have permission.']);
            }
          }
        }

        // Alterando outro usuário
        else {
          // bloqueada alteração nos seguintes usuários:
          $denyTypes = [
            Admin::TYPE_DEV,
            Admin::TYPE_ADMIN,
          ];
          if (in_array($target['type'], $denyTypes)) {
            $res->status(401)->send(['message'=>'You do not have permission.']);
          }

          $allowFields = [
            'status',
            'type',
            'email',
            'name',
            'user',
          ];
          // Se tentando alterar campo além do permido
          foreach($dataKeys as $key) {
            if (!in_array($key, $allowFields)) {
              return $res->status(401)->send(['message'=>'You do not have permission.']);
            }
          }

          $allowDataTypes = [
            Admin::TYPE_OPERATOR,
            Admin::TYPE_ADMIN,
          ]; 
          // Se tentando alterar type além do permido
          if (in_array("type", $dataKeys) && !in_array($data['type'], $allowDataTypes)) {
            $res->status(401)->send(['message'=>'You do not have permission.']);
          }
        }
      break;
      
      case Admin::TYPE_OPERATOR:
        // bloqueia alterar outro usuário
        if (!$itsMe) {
          $res->status(401)->send(['message'=>'You do not have permission.']);
        }
        // se tentando alterar campo além do permido
        $allowFields = [
          'email',
          'name',
          'user',
        ];
          // Se tentando alterar campos além do permido
          foreach($dataKeys as $key) {
          if (!in_array($key, $allowFields)) {
            return $res->status(401)->send(['message'=>'You do not have permission.']);
          }
        }
      break;

      default:
        $res->status(401)->send(['message'=>'You do not have permission.']);
      break;
    }

    /*
    if (!isset($data['email']) || !$data['email']) {
      $res->status(402)->send(['message'=>'Missin email.']);
    }

    $password = isset($data['password']) ? $data['password'] : false;
    if (!$password) {
      $res->status(402)->send(['message'=>'Missin password.']);
    }
    unset($data['password']);
    $data['hash'] = password_hash($password, PASSWORD_BCRYPT);
    //$res->send($data);

    $result = $model->update($data);
    
    if (!$result) {
      $res->status(500)->send(['message'=>'Operation failure.']);
    }*/

    $result = $model->update($data);

    if (!$result) {
      $res->status(500)->send(['error'=>"update failure."]);
    }

    $res->send($result);
  }
}