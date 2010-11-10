<?php
abstract class WssAPI {
  protected $user;
  protected $password;
  protected $trace;

  protected $soap;

  function WssAPI($url, $options) {
    $this->user = $options['login'];
    $this->password = $options['password'];
    $this->trace = $options['trace'];

    $optionsForSoapClient = array(
      'features' => SOAP_SINGLE_ELEMENT_ARRAY,
      'exceptions' => true
    );
    if(!empty($this->user)) { $optionsForSoapClient['login'] = $this->user; }
    if(!empty($this->password)) { $optionsForSoapClient['password'] = $this->password; }
    if(!empty($this->trace)) { $optionsForSoapClient['trace'] = $this->trace; }

    $this->soap = $options['ntlm'] ? new NTLM_SoapClient($url, $optionsForSoapClient) : new SoapClient($url, $optionsForSoapClient);
  }

  function __setCookie($label, $value) {
    $this->soap->__setCookie($label, $value);
  }

  function getLastRequest() {
    return $this->soap->__getLastRequest();
  }

  function getLastRequestHeaders() {
    return $this->soap->__getLastRequestHeaders();
  }

  function getLastResponse() {
    return $this->soap->__getLastResponse();
  }

  function getLastResponseHeaders() {
    return $this->soap->__getLastResponseHeaders();
  }
}

/**
 * Roughly copied from http://www.php.net/manual/en/soapclient.soapclient.php#97029 then modified to suit
 * @throws Exception
 *
 */
class NTLM_SoapClient extends SoapClient {
  protected $user;
  protected $password;

  public function __construct($wsdl, $options = array()) {
    if(empty($options['login']) || empty($options['password'])) {
      throw new Exception('login and password options must be supplied to SoapClient for NTLM authentication.');
    }

    $this->user = $options['login'];
    $this->password = $options['password'];

    parent::__construct($wsdl, $options);
  }

  protected function callCurl($url, $data) {
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_FAILONERROR, true);
    curl_setopt($handle, CURLOPT_USERAGENT, 'PHP SoapClient modified for NTLM');
    curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-type: text/xml'));
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
    curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
    curl_setopt($handle, CURLOPT_USERPWD, $this->user.':'.$this->password);
    $response = curl_exec($handle);
    if(empty($response)) {
      throw new SoapFault('CURL Error: '.curl_error($handle), curl_errno($handle));
    }
    curl_close($handle);
    return $response;
  }

  public function __doRequest($request,$location,$action,$version,$one_way=0) {
    // TODO: Why am I not using the other input parameters, and what is the consequence?
    return $this->callCurl($location,$request);
  }
}