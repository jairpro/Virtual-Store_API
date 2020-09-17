<?php

require_once dirname(__FILE__)."/../../modules/my-jwt/.config.php";

class Auth {

  protected $secret;

  function __construct($secret=null) {
    $this->secret = isset($secret) ? $secret : MY_JWT_SECRET;
  }

  function execute($req, $res) {
    $req = $req ? $req : Request::getInstance();
    $res = $res ? $res : Response::getInstance();
    $authHeader = $req->headers('authorization');

    if (!$authHeader) {
      $res->status(401)->json([ 'message'=> 'Token not provided.' ]);
    }

    $authElements = is_string($authHeader) ? explode(" ", $authHeader) : [];
    $token = isset($authElements[1]) ? $authElements[1] : "";

    if (!$token) {
      $res->status(401)->json(['message' => 'Token not found.']);
    }
    
    $jwt = MyJWT::getInstance();
    if (!$jwt->validate($token, $this->secret)) {
      $res->status(401)->json(['message' => $jwt->getMessage()]);
    }

    $bl = MyJwtBlacklist::getInstance();
    if ($bl->isRevoked($token)) {
      $res->status(401)->json(['message' => 'The Token is revoked.']);
    }
  
    $result = $jwt->getResult();
    $payload = $result['payload'];
    //$res->json($payload);
    $id = isset($payload['id']) ? $payload['id'] : (isset($payload['user_id']) ? $payload['user_id'] : false);
    $req->userId = $id;
    $req->token = $token;
    
    return true;
  }
}