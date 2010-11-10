<?php
require_once('wss-api-nusoap.class.inc.php');
class WssAuthAPI extends WssAPI {
  public function __construct($siteUrl, $options) {
    parent::__construct($siteUrl.'/_vti_bin/Authentication.asmx?wsdl', $options);
  }

  function Login($username, $pass) {
    $params = array('username' => $username, 'password' => $password);
    $result = $this->soap->Login($params);

    return $result;
  }

  function Mode() {
    return $this->soap->Mode();
  }
}