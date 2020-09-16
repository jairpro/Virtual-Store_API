<?php

$dir = dirname(__FILE__)."/";
require_once $dir."../../modules/my-sendgrid/.env.php";

class ForgotPasswordController {

  protected $modelClass = 'Admin';
  protected $fieldUser = 'user';
  protected $fieldEmail = 'email';
  protected $fieldName = 'name';

  function store($req, $res) {
    $data = $req->body();
    if (!is_array($data)) {
      $res->status(400)->send(['error' => "Invalid request body."]);
    }

    if (!isset($data['user'])) {
      $res->status(400)->send(['error' => "Missing the user field."]);
    }

    $user = $data['user'];

    $model = new $this->modelClass();

    $found = $model->findOne([
      'or'=>[
        $this->fieldUser => $user,
        $this->fieldEmail => $user
      ]
    ]);

    if (!$found) {
      $res->status(400)->send(['message' => "User not found."]);
    }
  
    $toEmail = isset($found[$this->fieldEmail]) ? $found[$this->fieldEmail] : false; 

    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
      $res->status(400)->send(['message' => "User does not have valid email."]);
    }
  
    $toName = isset($found[$this->fieldName]) ? $found[$this->fieldName] : "";


    $jwt = new MyJWT();
    $jwt->timeout(RESET_JWT_TIMEOUT);
    $tokenData = [
      'id' => $found['id']
    ];
    $resetToken = $jwt->generateToken($tokenData, RESET_JWT_SECRET);

    $cancelToken = '';

    $appUrl = MAIL_APP_URL;
    $appFriendlyDomain = MAIL_APP_FRIENDLY_DOMAIN;
    
    // LOGO
    // ====
    $serviceUrl = MAIL_SERVICE_URL;
    $logoAlt = MAIL_SERVICE_NAME;
    $logoSrc = MAIL_LOGO_SRC;

    // RESET
    // =====
    $resetUrl = MAIL_RESET_ADMIN_URL.$resetToken;
  
    $replyToEmail = MAIL_REPLY_TO_EMAIL;
    $fromNameSignature = MAIL_FROM_NAME_SIGNATURE;
    $fromName = MAIL_FROM_NAME;
    $slogan = MAIL_SLOGAN;
    
    $fromDetails = MAIL_FROM_DETAILS;
    
    // CANCEL
    // ======
    $cancelUrl = MAIL_CANCEL_URL.$cancelToken;

    $html = "
<html>
<head>
<style>

</style>
</head>
<body>
<a href=\"$serviceUrl\"><img alt=\"$logoAlt\" src=\"$logoSrc\"></a>
<br>
<p>Olá $toName,
<p>Uma solicitação foi recebida para alterar a senha de sua conta $fromName.
<br>
<p><a href=\"$resetUrl\">Redefinir senha</a>
<br> 
<p>Se você não iniciou esta solicitação, entre em contato conosco imediatamente em $replyToEmail.
<br>
<p>Obrigado,
<br>$fromNameSignature
<br>
<p>$fromName
<br>$slogan
<br>
<p>$fromDetails
<br>
<p>Email enviado usando <a href=\"$appUrl\">$appFriendlyDomain</a> . 
".
($cancelToken ? ("<p>Para cancelar <a href=\"$cancelUrl\">clique aqui</a>".PHP_EOL) : "")
."
</body>
</html>
";

    $sendgrid = new MySendgrid();
    $result = $sendgrid->send([
      'toEmail' =>  $toEmail,
      'toName' =>  $toName,

      //'subject' =>  "Sua solicitação de redefinição de senha do SendGrid",
      'subject' =>  "Recuperação de senha",

      //'plain' =>  strip_tags($html),
      'html' =>  $html,
    ]);

    if (!$result) {
      $res->status(500)->send(['error' => "The message can not be sent."]);
    }

    return $res->send(['message'=>"Message sent successfully"]);
  }
}