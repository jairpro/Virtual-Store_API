<?php

$dir = dirname(__FILE__)."/";
require $dir.".config.php";
require $dir.'vendor/autoload.php';

use Carbon\Carbon;

class MyJWT {
  protected static $instance;
  protected $data;
  protected $token;
  protected $message;
  protected $timeout; // expiração do token em segundos
  protected $result;
  
  function __construct() {
    $this->timeout = defined("MY_JWT_TIMEOUT") ? MY_JWT_TIMEOUT : 60*60*1; // expiração do token em segundos
  }

  static function getInstance() {
    return self::$instance;
  }

  function timeout($t=null) {
    if (isset($t)) {
      $this->timeout = $t;
    }
    return $this->timeout;
  }

  function getMessage() {
    return $this->message;
  }

  function getResult() {
    return $this->result;
  }

  function data($data=null) {
    if (isset($data)) {
      $this->data = $data;
    }
    return $data;
  }
  
  function getToken() {
    return $this->token;
  }

  function validate($token, $key=null) {
    // get the local secret key
    //$secret = getenv('SECRET');
    $secret = isset($key) ? $key : MY_JWT_SECRET;
    
    //$token = isset($token) ? $token : $this->token;
    
    $this->result = [
      'valid' => false
    ];

    if (!$token) {
      $this->result['message'] = 'Please provide a key to verify.';
      $this->message = $this->result['message'];
      return false;
    }

    $jwt = $token;

    // split the token
    $tokenParts = explode('.', $jwt);

    if (!is_array($tokenParts) || count($tokenParts)<3) {
      $this->result['message'] = 'Malformed token.';
      $this->result['tokenParts'] = $tokenParts;
      $this->result['jwt'] = $jwt;
      $this->result['token'] = $token;
      $this->message = $this->result['message'];
      return false;
    }

    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];

    // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
    $expiration = Carbon::createFromTimestamp(json_decode($payload)->exp);
    $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

    // build a signature based on the header and payload using the secret
    $base64UrlHeader = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode($payload);
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    // verify it matches the signature provided in the token
    $signatureValid = ($base64UrlSignature === $signatureProvided);

    //echo "Header:\n" . $header . "\n";
    //echo "Payload:\n" . $payload . "\n";

    $ok = true;
    $message = '';

    if (!$signatureValid) {
      $message = 'Invalid Token signature.';
      $ok = false;
    }
    else {
      if ($tokenExpired) {
        $message = 'Token has expired.';
        $ok = false;
      }
      else {
        $message = 'Token is valid!';
      }
    }

    $this->message = $message;

    $this->result = [
      'valid' => $signatureValid && !$tokenExpired,
      'signature' => $signatureValid,
      'expired' => $tokenExpired,
      'message' => $message, 
      'payload' => json_decode($payload, true),
      'header' => json_decode($header, true)
    ];
    return $ok;
  }
  
  function generateToken($data=null, $key=null) {
    
    // get the local secret key
    //$secret = getenv('SECRET');
    //require dirname(__FILE__)."/.config.php";
    $secret = isset($key) ? $key : MY_JWT_SECRET;
    
    // Create the token header
    $header = json_encode([
      'typ' => 'JWT',
      'alg' => 'HS256'
    ]);
    
    $dataPayload = array_merge(
      is_array($data) ? $data : (is_array($this->data) ? $this->data : []), [
        //'user_id' => 1,
        //'role' => 'admin',
        //'exp' => 1593828222
        //'exp' => 1602115200
        'exp' => time()+$this->timeout
      ]
    );
      
    // Create the token payload
    $payload = json_encode($dataPayload);
    
    // Encode Header
    $base64UrlHeader = self::base64UrlEncode($header);

    // Encode Payload
    $base64UrlPayload = self::base64UrlEncode($payload);
    
    // Create Signature Hash
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);

    // Encode Signature to Base64Url String
    $base64UrlSignature = self::base64UrlEncode($signature);

    // Create JWT
    $this->token = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    //echo "Your token:\n" . $this->token . "\n";
    return $this->token;
  }

  // bootstrap:

  // PHP has no base64UrlEncode function, so let's define one that
  // does some magic by replacing + with -, / with _ and = with ''.
  // This way we can pass the string within URLs without
  // any URL encoding.
  static function base64UrlEncode($text) {
    return str_replace(
      ['+', '/', '='],
      ['-', '_', ''],
      base64_encode($text)
    );
  }

  static function generateKey() {
    return bin2hex(random_bytes(32));
  }

  static function init() {
    self::$instance = new MyJWT();
  }
}
MyJWT::init();