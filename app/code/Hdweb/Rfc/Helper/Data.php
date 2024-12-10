<?php

namespace Hdweb\Rfc\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_logger;
	protected $scopeConfig;
	protected $_timezoneInterface;
	protected $objectManager;
	protected $_resouceConnection;
	protected $rfcCollection;
    protected $productCollectionFactory;
    protected $_filesystem;
    protected $_storeManager;
    protected $_indexerFactory;
    protected $_indexerCollectionFactory;
	protected $_messageManager;
	const ERP_URL = 'https://api.businesscentral.dynamics.com/v2.0/671a2e1d-a893-4a6d-afbf-b73562b1a7f5/YallaUAT/WS/My%20Company/Codeunit/YallaWS';
	const USERNAME = 'APNT';
	const PASSWORD = 'GBcV5BR0LVU+j7p2oU8jBwGrqAgfmijEqCT4KOzYuxc=';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
		\Magento\Framework\App\ResourceConnection $resouceConnection,
		\Hdweb\Rfc\Model\ResourceModel\Rfc\Collection $rfcCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Filesystem $_filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_logger = $logger;
		$this->objectManager = $objectManager;
		$this->scopeConfig = $scopeConfig;
		$this->_timezoneInterface = $timezoneInterface;
		$this->_resouceConnection = $resouceConnection;
		$this->rfcCollection = $rfcCollection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_filesystem = $_filesystem;
        $this->_storeManager = $storeManager;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
		$this->_messageManager  = $messageManager;
    }

	public function createErpProduct($product_id = null)
	{
		if($product_id){
			$collection = $this->productCollectionFactory->create()
						->addAttributeToSelect('*')
						->addAttributeToFilter('entity_id', array('eq' => $product_id));//need to change as per erp status
		}else{
			$collection = $this->productCollectionFactory->create()
					->addAttributeToSelect('*');
		}
					
		$connection = $this->_resouceConnection->getConnection();	
		$catalogProductEntityVarcharTable = $this->_resouceConnection->getTableName('catalog_product_entity_varchar');
		$erpProductRefAttrId = 184;
		$erpProductDateAttrId = 185;
		
		$url = self::ERP_URL;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

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
		
		$rfcUrl = $url;
		$rfcUsername = '';
		$rfcPassword = '';
		$method = 'Auto';
		$rfcEnable = 1;
		$rfc = $this->objectManager->create('Hdweb\Rfc\Model\Rfc');
		$rfc->setData('rfc_name','Dynamics Product Update');
		$rfc->setData('rfc_url', $rfcUrl);
		$rfc->setData('rfc_username', $rfcUsername);
		$rfc->setData('rfc_password', $rfcPassword);
		$rfc->setData('rfc_datetime', $this->getTodaysDate());
		$rfc->setData('rfc_enable', $rfcEnable);
		$rfc->setData('rfc_status', 'Running');
		$rfc->setData('rfc_run_method', $method);
		$rfc->save();
		$rfcid = $rfc->getRfcId();
		
		$totalcount = 0;
		$successcount = 0;
		$failedcount = 0;
		
		$responseRowData = array();
		$responseRowData[] = array('SKU','ERP Product Ref','Created Date','Status','Message');
		foreach($collection as $productData){
			$productId = $productData->getId();
			//$product = $this->getProductData($productId);
			$productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');
			$product = $productRepository->getById($productId); 
			$sku = $product->getSku();
			$title = ucwords(strtolower($product->getName()));
			$title = substr($title, 0, 50);
			$description = ucwords(strtolower($product->getName()));
			$description = substr($description, 0, 50);
			$basePrice = $product->getPrice();
			$price  = $basePrice * (5/100);
			$finalPrice = $basePrice + $price;
			$brand = $product->getResource()->getAttribute("mgs_brand")->getFrontend()->getValue($product);
			$brand = substr($brand, 0, 10);
			$width = $product->getResource()->getAttribute("width")->getFrontend()->getValue($product);
			$height = $product->getResource()->getAttribute("height")->getFrontend()->getValue($product);
			$rimSize = $product->getResource()->getAttribute("rim")->getFrontend()->getValue($product);
			$loadIndex = $product->getResource()->getAttribute("load_index")->getFrontend()->getValue($product);
			$speedIndex = $product->getResource()->getAttribute("speed_index")->getFrontend()->getValue($product);
			$pattern = $product->getResource()->getAttribute("pattern")->getFrontend()->getValue($product);
			$pattern = substr($pattern, 0, 10);
			$year = $product->getResource()->getAttribute("year")->getFrontend()->getValue($product);
			$countryOfOrigin = $product->getResource()->getAttribute("manufacturer")->getFrontend()->getValue($product);
			if($countryOfOrigin != ''){
				$countryOfOrigin = $product->getResource()->getAttribute("manufacturer")->getFrontend()->getValue($product);
			}else{
				$countryOfOrigin = 'UAE';
			}
			//$pricewithTax = $currency.number_format($finalPrice, 2);
			$status = $product->getStatus();
			$blocked = 0;
			//product image
			//$store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();  
			//$imageLink = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
	
	$data = <<<DATA
<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="urn:microsoft-dynamics-schemas/codeunit/YallaWS">
  <soapenv:Header />
  <soapenv:Body>
    <ws:SendItemData>
      <ws:referenceNo></ws:referenceNo>
      <ws:itemNo>$sku</ws:itemNo>
      <ws:description>$description</ws:description>
      <ws:category>PLT</ws:category>
      <ws:subCategory>SUV</ws:subCategory>
      <ws:unitPrice>$finalPrice</ws:unitPrice>
      <ws:brand>$brand</ws:brand>
      <ws:genProdPostingGroup>TYRE</ws:genProdPostingGroup>
      <ws:inventoryPostingGroup>TYRE</ws:inventoryPostingGroup>
      <ws:vendorItemNumber>VIN</ws:vendorItemNumber>
      <ws:countryOfOrigin>$countryOfOrigin</ws:countryOfOrigin>
      <ws:width>$width</ws:width>
      <ws:height>$height</ws:height>
      <ws:rimSize>$rimSize</ws:rimSize>
      <ws:loadIndex1>$loadIndex</ws:loadIndex1>
      <ws:loadIndex2></ws:loadIndex2>
      <ws:speed></ws:speed>
      <ws:pattern>$pattern</ws:pattern>
      <ws:oEMark>oEMark</ws:oEMark>
      <ws:onlineItemNo>$sku</ws:onlineItemNo>
      <ws:year>$year</ws:year>
      <ws:blocked>$blocked</ws:blocked>
    </ws:SendItemData>
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
		//$logger->info(print_r($responseArray, true));
		//echo '<pre>';print_r($responseArray);die;
		$faultValue = '';
		$faultstring = '';
		if(isset($responseArray['sBody'])){
			$faultValue = $responseArray['sBody']['sFault']['faultcode'];
			$faultstring = $responseArray['sBody']['sFault']['faultstring'];
			date_default_timezone_set('Asia/Dubai');
			$creationDate = date('Y/m/d h:i: A');
			$failedcount++;
			$responseRowData[] = array($sku,'N/A',$creationDate,'Failed',$faultstring);
			$this->_messageManager->addError(__($faultstring.'. Please check in respective sub-master at ERP Software.'));
		}else{
			$returnValue = $responseArray['SoapBody']['SendItemData_Result']['return_value'];
			$referenceNo = $responseArray['SoapBody']['SendItemData_Result']['referenceNo'];
			if($returnValue == 'Sucess'){
				if($referenceNo){
					date_default_timezone_set('Asia/Dubai');
					$creationDate = date('Y/m/d h:i: A');
					$updateErpProductHrefsql = "UPDATE ".$catalogProductEntityVarcharTable. " SET value = '".$referenceNo."' WHERE attribute_id = ".$erpProductRefAttrId." AND entity_id = ".$productId."";
					
					$updateErpProductDatesql = "UPDATE ".$catalogProductEntityVarcharTable. " SET value = '".$creationDate."' WHERE attribute_id = ".$erpProductDateAttrId." AND entity_id = ".$productId."";

					$connection->query($updateErpProductHrefsql);
					$connection->query($updateErpProductDatesql);
					$successcount++;
					$responseRowData[] = array($sku,$referenceNo,$creationDate,$returnValue,'Updated');
					$this->_messageManager->addSuccess(__($referenceNo.' Item created successfully.'));
				}
			}else{
				$this->_messageManager->addError(__($returnValue.'. Please check in respective sub-master at ERP Software.'));
			}		
		}
		$totalcount++;
		if(count($responseRowData) > 1){
			$this->_createcsvfile($responseRowData, $rfcid);
		}
		$rfc = $this->objectManager->create('Hdweb\Rfc\Model\Rfc')->load($rfcid);
		$rfc->setData('rfc_datetime', $this->getTodaysDate());
		$rfc->setData('rfc_status', 'Success');
		$rfc->setData('rfc_total_record', $totalcount);
		$rfc->setData('rfc_total_sucess', $successcount);
		$rfc->setData('rfc_total_fail', $failedcount);
		$rfc->save();
	}
		curl_close($curl);
	}
	
	public function getStore()
    {
        return $this->_storeManager->getStore();
    }

	public function getTodaysDate()
	{
        $localeTimezone = $this->_timezoneInterface->getConfigTimezone('store', $this->getStore());
        date_default_timezone_set($localeTimezone);
		return $this->_timezoneInterface->date()->format('Y-m-d H:i:s');
	}

    public function _createcsvfile($responseRow, $rfcid)
	{
        if(count($responseRow) > 0){

            $csvMediapath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().'rfcfiles/';
            if(!is_dir($csvMediapath)){
                $csvMediapath = mkdir($csvMediapath);
            }

            $outputFile = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().'rfcfiles/'."Dynamics_Product_Update_".$this->getTodaysDate().".csv";

            $handle = fopen($outputFile, 'w');
            foreach ($responseRow as $response) {
                fputcsv($handle, $response);
            }

            $rfc = $this->objectManager->create('Hdweb\Rfc\Model\Rfc')->load($rfcid);
            $rfc->setData('rfc_manual_path', "Dynamics_Product_Update_".$this->getTodaysDate().".csv");
            $rfc->save();
        }
    }
}