<?php

namespace Hdweb\Insurance\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
  const XML_PATH_CLIENT_ID = 'insurance/general/client_id';
  const XML_PATH_CLIENT_SECRET = 'insurance/general/client_secret';
  const XML_PATH_API_CALL_BASEURL = 'insurance/general/api_call_baseurl';
  const XML_PATH_INSURANCE_SUBMIT_EMAIL = 'insurance/general/insurance_submit_email_template';
  protected $_storeManager;
  protected $_scopeConfig;
  protected $transportBuilder;

  public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    TransportBuilder $transportBuilder
  ) {
    $this->_storeManager = $storeManager;
    $this->_scopeConfig  = $scopeConfig;
    $this->transportBuilder = $transportBuilder;
    parent::__construct($context);
  }

  public function getClientId()
  {
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    return $this->_scopeConfig->getValue(self::XML_PATH_CLIENT_ID, $storeScope);
  }

  public function getClientSecret()
  {
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    return $this->_scopeConfig->getValue(self::XML_PATH_CLIENT_SECRET, $storeScope);
  }

  public function getAPIcallBaseUrl()
  {
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    return $this->_scopeConfig->getValue(self::XML_PATH_API_CALL_BASEURL, $storeScope);
  }

  public function getDataUsingCURL($url)
  {
    //$url = "https://insured.ae/get-car-model-years";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
  }

  public function insuranceAuth()
  {
    $clienId = $this->getClientId();
    $clienSecret = $this->getClientSecret();
    $apiCallBaseUrl = $this->getAPIcallBaseUrl();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $apiCallBaseUrl . 'api/v2/auth',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
            "grant_type": "client_credentials",
            "client_id": "' . $clienId . '",
            "client_secret": "' . $clienSecret . '"
        }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  public function getCarTypes($access_token)
  {
    $apiCallBaseUrl = $this->getAPIcallBaseUrl();
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $apiCallBaseUrl . 'api/v2/master/car',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  public function getDataFromAPI($url)
  {
    $authResponse = $this->insuranceAuth();
    $authResponseArr = json_decode($authResponse);
    $access_token = $authResponseArr->response->access_token;

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $access_token
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  public function sendEmail($postData)
  {
    $store = $this->_storeManager->getStore();

    $templateVars = [
      'insurance_type' => $postData['policy_type'],
      'name' => $postData['customer_name'],
      'email' => $postData['email'],
      'mobile' => $postData['mobile_number'],
      /* 'make' => $postData['car_make'],
            'model' => $postData['car_model'],
            'year' => $postData['car_model_year'],
            'vehicle_plate_no' => $postData['vehicle_plate_no'], */
      // Add other variables as needed
    ];

    $templateOptions = [
      'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // or \Magento\Framework\App\Area::AREA_ADMINHTML
      'store' => $store->getId(),
    ];
    $email = $this->_scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
    $name = $this->_scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
    $from = array('email' => $email, 'name' => $name);
    $to = $this->_scopeConfig->getValue('contact/email/recipient_email', ScopeInterface::SCOPE_STORE);
    if ($to == '') {
      $to = 'vicky.hdit@gmail.com';
    }
    //$to = 'vicky.hdit@gmail.com';
    $copy_to_bcc = array('nikhil.hdit@gmail.com');
    $copy_to_cc = 'dhruv.hdit@gmail.com';
    $insuranceSubmitEmailTemplate = $this->_scopeConfig->getValue(self::XML_PATH_INSURANCE_SUBMIT_EMAIL, ScopeInterface::SCOPE_STORE);

    // Load the email template
    $this->transportBuilder
      ->setTemplateIdentifier($insuranceSubmitEmailTemplate)
      ->setTemplateOptions($templateOptions)
      ->setTemplateVars($templateVars)
      ->setFrom($from)
      ->addTo($to)/* 
            ->addCc($copy_to_cc)
            ->addBcc($copy_to_bcc) */;

    // Send the email
    $transport = $this->transportBuilder->getTransport();
    $transport->sendMessage();

    return $this;
  }
}
