<?php
namespace Hdweb\Rfc\Controller\Adminhtml\rnr;

class Createorder extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $_scopeConfig;
    protected $_order;
    protected $_product;
    protected $_storeManager;
    protected $_dir;
    protected $userFactory;
    protected $orderRepository;
    protected $customerRepositoryInterface;
    protected $_countryFactory;
    protected $_messageManager;
	protected $storeRepository;
	const ERP_URL = 'https://api.businesscentral.dynamics.com/v2.0/671a2e1d-a893-4a6d-afbf-b73562b1a7f5/YallaUAT/WS/My%20Company/Codeunit/YallaWS';
	const USERNAME = 'APNT';
	const PASSWORD = 'GBcV5BR0LVU+j7p2oU8jBwGrqAgfmijEqCT4KOzYuxc=';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
		\Ecomteck\StoreLocator\Api\StoresRepositoryInterface $storeRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory           = $resultPageFactory;
        $this->_scopeConfig                = $scopeConfig;
        $this->_order                      = $order;
        $this->_product                    = $product;
        $this->_storeManager               = $storeManager;
        $this->_dir                        = $dir;
        $this->userFactory                 = $userFactory;
        $this->orderRepository             = $orderRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->_countryFactory             = $countryFactory;
        $this->_messageManager             = $messageManager;
		$this->storeRepository = $storeRepository;
    }
    public function execute()
    {
        $data           = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $order_id       = $this->getRequest()->getParam('order_id');
        $order          = $this->_order->load($order_id);
		
		if(isset($data['erp_inventory_location'])){
			if($data['erp_inventory_location'] != ''){
				$order->setErpInventoryLocation($data['erp_inventory_location']);
				$order->save();
				$this->_messageManager->addSuccess(__('ERP Inventory Location saved successfully.'));
			}else{
				$this->_messageManager->addError(__('Select ERP Inventory Location.'));
			}
			$this->_redirect('sales/order/view', array('order_id' => $order_id));
		}
		else{
			
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $noOfLines = $order->getTotalItemCount(); //count($order->getAllVisibleItems());
		$billingAddress  = $order->getBillingAddress();
		$customerName = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
		$customerEmail = $order->getCustomerEmail();
		$phoneNo = $billingAddress->getTelephone(); // 9 digit mobile number validation
		$address = $billingAddress->getStreet()[0];
		$address2 = $billingAddress->getCity();
		$city = $billingAddress->getCity();
		$CreateDate    = $order->getCreatedAt();
		$datetime      = strtotime($CreateDate);
		$documentDate = date('m-d-y', $datetime);
		$onlineOrderNo = $order->getIncrementId();
		
		$pickupStoreId = $order->getPickupStore();
		$installerObj = $this->storeRepository->getById($pickupStoreId);
		$installerCode = '';
		if($installerObj->getErpInstallerCode() != '' ){
			$installerCode = $installerObj->getErpInstallerCode();
		}else{
			$installerCode = 'EUT AUH'; // get installed id and make it conditional
		}
		$vehicleMake = $order->getMake();
		$vehicleModel = $order->getModel();
		$vehicleYear = $order->getYear();
		$vehicleNo = $order->getPlate();
		$vehicleKMS = 1;
		$orderpayment       = $this->orderRepository->get($order_id);
		$payment            = $order->getPayment();
		
		$paymentReferanceNo = '';
		$paymentMethod = '';
		if($payment->getLastTransId() != ''){
			$paymentMethod  = 'Online';
			$parentTranData = $payment->getAdditionalInformation();
			if(isset($parentTranData['checkout_id'])){
				$paymentReferanceNo = $parentTranData['checkout_id'];	
			}else{
				$paymentReferanceNo = $payment->getLastTransId();
			}
		}else{
			$paymentMethod = 'Cash';
			$paymentReferanceNo = 'Cash on delivery';
		}
		
		$totalExclVATAmount = $order->getSubtotal();
		$totalVAT = $order->getTaxAmount();
		$totalIncluVATAmount = $order->getGrandTotal();
		$vATBusPostingGroup = '';
		switch ($city) {
		  case "Ajman":
			$vATBusPostingGroup = 'AJM_VAT';
			break;
		  case "Abu Dhabi":
			$vATBusPostingGroup = 'AUH_VAT';
			break;
		  case "Dubai":
			$vATBusPostingGroup = 'DXB_VAT';
			break;
		  case "Fujairah":
			$vATBusPostingGroup = 'FUJ_VAT';
			break;
		  case "Ras Al Khaimah":
			$vATBusPostingGroup = 'RAK_VAT';
			break;
		  case "Sharjah":
			$vATBusPostingGroup = 'SHJ_VAT';
			break;
		  case "Umm Al Quwain":
			$vATBusPostingGroup = 'UAQ_VAT';
			break;		
		  default:
			$vATBusPostingGroup = 'DXB_VAT';
		}
		
		if(empty($order->getErpSalesHeaderRef())){
		/* Start Sales Header Request Call */ 
		$url = self::ERP_URL;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		/* $headers = array(
		   "SOAPAction: ".$url."",
		   "Authorization: Basic QVBOVDpHQmNWNUJSMExWVStqN3Ayb1U4akJ3R3JxQWdmbWlqRXFDVDRLT3pZdXhjPQ==",
		   "Content-Type: application/xml",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); */
		
		$soapUser = self::USERNAME;  //  username
		$soapPassword = self::PASSWORD; // password
		
		$headers = array(
		   "SOAPAction: ".$url."",
		 //  "Authorization: Basic YXBudDpHQmNWNUJSMExWVStqN3Ayb1U4akJ3R3JxQWdmbWlqRXFDVDRLT3pZdXhjPQ==",
		   "Content-Type: application/xml",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

$data = <<<DATA
<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="urn:microsoft-dynamics-schemas/codeunit/YallaWS">
  <soapenv:Header />
  <soapenv:Body>
    <ws:SendSalesHeaderData>
      <ws:referenceNo></ws:referenceNo>
      <ws:type>Order</ws:type>
      <ws:customerName>$customerName</ws:customerName>
      <ws:phoneNo>$phoneNo</ws:phoneNo>
      <ws:mobileNo>$phoneNo</ws:mobileNo>
      <ws:address>$address</ws:address>
      <ws:address2>$address2</ws:address2>
      <ws:city>$city</ws:city>
      <ws:country>UAE</ws:country>
      <ws:documentDate>$documentDate</ws:documentDate>
      <ws:onlineOrderNo>$onlineOrderNo</ws:onlineOrderNo>
      <ws:installerCode>$installerCode</ws:installerCode>
      <ws:vehicleMake>$vehicleMake</ws:vehicleMake>
      <ws:vehicleModel>$vehicleModel</ws:vehicleModel>
      <ws:vehicleYear>$vehicleYear</ws:vehicleYear>
      <ws:vehicleNo>$vehicleNo</ws:vehicleNo>
      <ws:vehicleKMS>$vehicleKMS</ws:vehicleKMS>
      <ws:division>PLT</ws:division>
      <ws:area>DXB01</ws:area>
      <ws:onlineItemNo></ws:onlineItemNo>
      <ws:paymentMethod>$paymentMethod</ws:paymentMethod>
      <ws:paymentReferanceNo>$paymentReferanceNo</ws:paymentReferanceNo>
      <ws:totalExclVATAmount>$totalExclVATAmount</ws:totalExclVATAmount>
      <ws:totalVAT>$totalVAT</ws:totalVAT>
      <ws:totalIncluVATAmount>$totalIncluVATAmount</ws:totalIncluVATAmount>
      <ws:vATBusPostingGroup>$vATBusPostingGroup</ws:vATBusPostingGroup>
      <ws:noOfLines>$noOfLines</ws:noOfLines>
	  <ws:email>$customerEmail</ws:email>
    </ws:SendSalesHeaderData>
  </soapenv:Body>
</soapenv:Envelope>
DATA;

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);

$resp = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $resp);
$xml = new \SimpleXMLElement($resp);
//echo '<pre>';print_r($xml);die;
$responseArray = json_decode(json_encode((array)$xml), TRUE);

$returnValue = '';
$referenceNo = '';
if(isset($responseArray['sBody'])){
	$returnValue = $responseArray['sBody']['sFault']['faultstring'];
}else{
	$returnValue = $responseArray['SoapBody']['SendSalesHeaderData_Result']['return_value'];
	$referenceNo = $responseArray['SoapBody']['SendSalesHeaderData_Result']['referenceNo'];
}	

/* End Sales Header Request Call */ 

if($returnValue == 'OK'){
	if($referenceNo){
		$order->setErpSalesHeaderRef($referenceNo);
		$order->save();
		$this->_messageManager->addSuccess(__('ERP Order Header generated successfully.'));
	}
}
curl_close($curl);
}	
	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/erp-log.log');
	$logger = new \Zend\Log\Logger();
	$logger->addWriter($writer);
	$logger->info('order erp header ref:- '.$order->getErpSalesHeaderRef());
	//$logger->info('returnValue:- '.$returnValue);
	$logger->info('=========================');
	
	if($order->getErpSalesHeaderRef() != '' || $returnValue == 'OK'){
		/* Start Sales Header Request Call */ 
		$url = self::ERP_URL;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		/* $headers = array(
		   "SOAPAction: ".$url."",
		   "Authorization: Basic QVBOVDpHQmNWNUJSMExWVStqN3Ayb1U4akJ3R3JxQWdmbWlqRXFDVDRLT3pZdXhjPQ==",
		   "Content-Type: application/xml",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); */
		
		$soapUser = self::USERNAME;  //  username
		$soapPassword = self::PASSWORD; // password
		
		$headers = array(
		   "SOAPAction: ".$url."",
		 //  "Authorization: Basic YXBudDpHQmNWNUJSMExWVStqN3Ayb1U4akJ3R3JxQWdmbWlqRXFDVDRLT3pZdXhjPQ==",
		   "Content-Type: application/xml",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		
		$i = 1;		
		
		$orderItems = $this->_objectManager->get('Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory');
		$inventoryLocation	= $order->getErpInventoryLocation(); //'L001';
	foreach ($order->getAllVisibleItems() as $item) {
		
		$_product = $this->_product->load($item->getProductId());
		$itemNo	= $_product->getErpSku();
		
		$qty	= $item->getQtyOrdered();
		$unitPriceWithoutVAT	= $item->getPrice();
		$discountPercent	= $item->getDiscountPercent();
		$discountAmount	= $item->getDiscountAmount();
		$netAmountWithoutVAT	= $item->getRowTotal();
		$vATAmount	= $item->getTaxAmount();
		$orderItemId = $item->getItemId();
		$erp_sales_order_item = $item->getErpSalesOrderItem();
		
		if(empty($erp_sales_order_item)){			
$data = <<<DATA
<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="urn:microsoft-dynamics-schemas/codeunit/YallaWS">
  <soapenv:Header />
  <soapenv:Body>
    <ws:SendSalesLineData>
      <ws:referenceNo></ws:referenceNo>
      <ws:onlineOrderNo>$onlineOrderNo</ws:onlineOrderNo>
      <ws:orderLineNo>$orderItemId</ws:orderLineNo>
      <ws:itemNo>$itemNo</ws:itemNo>
      <ws:inventoryLocation>$inventoryLocation</ws:inventoryLocation>
      <ws:qty>$qty</ws:qty>
      <ws:unitPriceWithoutVAT>$unitPriceWithoutVAT</ws:unitPriceWithoutVAT>
      <ws:discountPercent>$discountPercent</ws:discountPercent>
      <ws:discountAmount>$discountAmount</ws:discountAmount>
      <ws:netAmountWithoutVAT>$netAmountWithoutVAT</ws:netAmountWithoutVAT>
      <ws:vATAmount>$vATAmount</ws:vATAmount>
    </ws:SendSalesLineData>
  </soapenv:Body>
</soapenv:Envelope>
DATA;

		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		
		$resp = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $resp);
		$xml = new \SimpleXMLElement($resp);
		$responseArray = json_decode(json_encode((array)$xml), TRUE);
		//echo '<pre>';print_r($responseArray);die;
		$returnValue = $responseArray['SoapBody']['SendSalesLineData_Result']['return_value'];
		$referenceNo = $responseArray['SoapBody']['SendSalesLineData_Result']['referenceNo'];
		if($returnValue == 'OK'){
			if($referenceNo){
				$orderItem = $orderItems->create()->addFieldToFilter( 'item_id', $orderItemId)->getFirstItem();
				$orderItem->setErpSalesOrderItem($referenceNo);
				$orderItem->save();
			//	$haserp_sales_order_item = 1;
			$this->_messageManager->addSuccess(__($itemNo.' Item created successfully.'));
			}
		}else{
			$this->_messageManager->addError(__($returnValue));
		}
		
	}	
		$i++;		
	}	
	
	curl_close($curl);
	/* End Sales Header Request Call */ 
	
	$allItemsFetch = $this->checkAllItemStatus($order);
	if($allItemsFetch == 1){
		$order->setErpOrderStatus(1);
		$order->save();
		$this->_messageManager->addSuccess(__('ERP Order created successfully'));
	}
	
	$this->_redirect('sales/order/view', array('order_id' => $order_id));
	}else{
		$this->_messageManager->addError(__($returnValue));
		$this->_redirect('sales/order/view', array('order_id' => $order_id));
	}
  }	
}
	public function checkAllItemStatus($order)
    {
		$checkErpProductRef = array();
		
		foreach ($order->getAllVisibleItems() as $item) {
			if($item->getErpSalesOrderItem() != ''){
				$checkErpProductRef[] = 1;
			}else{
				$checkErpProductRef[] = 0;
			}
		}
        $erpProductref = '';
		if(in_array('0', $checkErpProductRef))
		{
		  $erpProductref = 0;
		}
		else
		{
		  $erpProductref = 1;
		}
		
        return $erpProductref;
    }
}