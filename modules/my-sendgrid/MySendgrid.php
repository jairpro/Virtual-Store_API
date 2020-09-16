<?php

$dir = dirname(__FILE__)."/";
require $dir.'vendor/autoload.php';
require_once $dir.'.env.php';

class MySendgrid {

  protected $response;
  protected $errorMessage;

  function getErrorMessage() {
    return $this->errorMessage;
  }

  function success() {
    return $this->response && $this->response->statusCode()==202;
  }

  function send($data) {
    $result = false;

    $fromEmail = isset($data['fromEmail']) ? $data['fromEmail'] : MAIL_FROM_EMAIL;
    $fromName = isset($data['fromName']) ? $data['fromName'] : MAIL_FROM_NAME;

    $toEmail = isset($data['toEmail']) ? $data['toEmail'] : "";
    $toName = isset($data['toName']) ? $data['toName'] : "";

    $subject = isset($data['subject']) ? $data['subject'] : "";
    $plain = isset($data['plain']) ? $data['plain'] : false;
    $html = isset($data['html']) ? $data['html'] : false;

    $email = new \SendGrid\Mail\Mail(); 
    $email->setFrom($fromEmail, $fromName);
    $email->setSubject($subject);
    $email->addTo($toEmail, $toName);
    if (is_string($plain)) {
      $email->addContent("text/plain", $plain);
    }
    if (is_string($html)) {
      $email->addContent("text/html", $html);
    }
    $sendgrid = new \SendGrid(SENDGRID_API_KEY);
    try {
        $this->response = $sendgrid->send($email);
        $result = $this->success();
        $this->errorMessage = null;
    } catch (Exception $e) {
        $this->errorMessage = 'Caught exception: '. $e->getMessage() .".";
    }
    return $result;
  }
}
