<?php
require_once('wss-api-nusoap.class.inc.php');
class WssListAPI extends WssAPI {
  public function __construct($siteUrl, $options=array()) {
    parent::__construct($siteUrl.'/_vti_bin/lists.asmx?wsdl', $options);
  }

  function GetListCollection() {
    return $this->soap->GetListCollection();
  }

  function GetList($listName) {
    $params = array('listName' => $listName);
    $result = $this->soap->GetList($params);

    return $result;
  }
  
  function GetListItems($listName, $query=null, $viewFields=null, $rowLimit=null, $queryOptions=null, $webId=null) {
    $retVal = new stdClass;    
    $params = array('listName' => $listName);
    if(!empty($query)) { $params['query'] = array('any' => $query); }
    if(!empty($viewFields)) { $params['viewFields'] = array('any' => $viewFields); }
    if(!empty($rowLimit)) { $params['rowLimit'] = $rowLimit; }
    if(!empty($queryOptions)) { $params['queryOptions'] = array('any' => $queryOptions); }
    if(!empty($webId)) { $params['webId'] = $webId; }
    
    $result = $this->soap->GetListItems($params);
    
    $responseXml = simplexml_load_string($result->GetListItemsResult->any);
    $namespaces = $responseXml->getNamespaces();
    $responseXml->registerXpathNamespace('rs', $namespaces['rs']);    
    $retVal->data = $responseXml->xpath('//rs:data');
    
    $responseXml->registerXpathNamespace('z', $namespaces['z']);
    $retVal->rows = $responseXml->xpath('//z:row');
    
    return $retVal; 
  }
  
  function UpdateListItems($listName, $updates) {
    //$retVal = new stdClass;
    $params = array(
      'listName' => $listName,
      'updates' => array('any' => $updates)
    );

    $result = $this->soap->UpdateListItems($params);
    
    return $result;
  }
}
?>