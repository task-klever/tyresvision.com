<?php
namespace Hdweb\Rfc\Controller\Adminhtml\rnr;

class Createimileorder extends \Magento\Backend\App\Action
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
        \Magento\Framework\Message\ManagerInterface $messageManager
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
    }
    public function execute()
    {
        $data           = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $order_id       = $this->getRequest()->getParam('order_id');
        $order          = $this->_order->load($order_id);
        // echo "<pre>";
        // print_r($order->getData());exit;
		
		// Generate iMile Order
		if ($order_id != '') {

			$customerCode  = '';
			$addressCode   = 'ADDR-HM';
			$CreateDate    = $order->getCreatedAt();
			$datetime      = strtotime($CreateDate);
			$date          = date('d-M-Y H:i:s', $datetime); //date('d-M-Y H:i:s');  // d.m.YYYY
			$requestedDate = date('Y-m-d', $datetime);
			$time          = date('H:i:s', $datetime); // HH:ss
			$SubTotal      = $order->getSubtotal();
			$taxAmount     = $order->getTaxAmount();
			$Company       = "Stop & Go Auto Accessories Trading LLC";
			$Branch        = "ECommStopnGo";
			$OrderType     = "B2C";
			$ValidityDate  = $date . 'T' . $time . '' . date('P', $datetime);

			$SaleExecutive = "";
			$SalesPersonId = $order->getSalesPersonId();
			if (isset($SalesPersonId) && !empty($SalesPersonId)) {
				$user = $this->userFactory->create();
				$user->load($SalesPersonId);
				$firstName     = $user->getFirstName();
				$lastname      = $user->getLastname();
				$SaleExecutive = $firstName . ' ' . $lastname;
			}
			echo "<pre>";
			$orderpayment       = $this->orderRepository->get($order_id);
			$payment            = $orderpayment->getPayment();
			
			$PaymentReferenceNo = '';
			if($payment->getLastTransId() != ''){
				$parentTranData = $payment->getAdditionalInformation();
				if(isset($parentTranData['checkout_id'])){
					$PaymentReferenceNo = $parentTranData['checkout_id'];	
				}else{
					$PaymentReferenceNo = $payment->getLastTransId();
				}
			}else{
				$PaymentReferenceNo = 'Cash on delivery';
			}
			$method             = $payment->getMethodInstance();
			$PaymentMethod      = $method->getTitle(); //
			$paymentMethodCode  = $method->getCode();
			$installer_detail   = $order->getInstallerDetail();
			$installer_details  = unserialize($installer_detail);
			$InstallerAddress   = $installer_details['street'] . ' ' . $installer_details['city'] . ' ' . $installer_details['region'];
			$billingAddress  = $order->getBillingAddress();
			$consigneeContact = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
			$customerId   = $order->getCustomerId();
			$country            = $this->_countryFactory->create()->loadByCode($billingAddress->getCountryId());
            $countryName        = $country->getName();
			
			$CustomerCode = "";
			$totalQty = 0;
			$itemCode = array();
			foreach ($order->getAllVisibleItems() as $item) {

				$_product = $this->_product->load($item->getProductId());
				// $productionYear = $_product->getAttributeText('production_year');
				$gcCode     = $_product->getGcItemCode();
				$qtyOrdered = $item->getQtyOrdered();
				$totalQty += $item->getQtyOrdered();
				$price      = $item->getPrice();
				$itemCode[]   = $_product->getSku();
			}
			
			$skuName = '';
			if(count($itemCode) > 1){
				$skuName = implode(";", $itemCode);
			}else{
				$skuName = $itemCode[0];
			}
			
			$collectingMoney = '';
			$paymentMethodName = '';
			if($paymentMethodCode == 'cashondelivery'){
				$collectingMoney = number_format($order->getGrandtotal(), 2, '.', '');
				$paymentMethodCode = 'COD';
			}else{
				$collectingMoney = 1;
				$paymentMethodCode = 'PPD';
			}
			
			$orderData          	 = array(
				"sign"          	 => "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALKzHHqoHr2r1CVY",
				"signMethod"         => "simpleKey", // simpleKey or md5 if using md5 sign
				"param"    			 => array(
					"dispatchDate"   		=> date("Y/m/d"),
					"orderCode"      		=> $order->getIncrementId(), //$order->getIncrementId(),
					"orderType"      		=> "100",
					"deliveryType" 			=>  "Delivery",
					"consignor" 			=>  "tyresonline",
					"consignee" 			=>  "",
					"consigneeContact" 		=>  $consigneeContact,
					"consigneeMobile" 		=>  $billingAddress->getTelephone(),
					"consigneePhone" 		=>  $billingAddress->getTelephone(),
					"serviceTime" 			=>  "",
					"consigneeCountry" 		=>  "UAE",
					"consigneeProvince" 	=>  "",
					"consigneeCity" 		=>  $billingAddress->getCity(),
					"consigneeArea" 		=>  "",
					"consigneeAddress" 		=>  $billingAddress->getStreet()[0],
					"paymentMethod" 		=>  $paymentMethodCode,
					"goodsValue"		 	=>  $collectingMoney, //need to confirm
					"collectingMoney" 		=>  $collectingMoney, //need to confirm
					"totalCount" 			=>  $totalQty,
					"totalWeight" 			=>  "3.500", //need to confirm
					"totalVolume"			=>  1, //need to confirm
					"skuTotal" 				=>  $totalQty,
					"skuName" 				=>  $skuName, //"test2;test3;",
					"deliveryRequirements" 	=>  ""
				),
				"customerId"   		 => "9900011",
				"format" 			 => "json",
				"method" 			 => "createOrder"
			);
			$orderRequest = json_encode($orderData);
			//echo '<pre>';print_r($orderRequest);die;
			$createOrderResponseData = $this->_objectManager->create('Hdweb\Rfc\Helper\Data')->getiMileCreateOrder($orderRequest);
			//echo '<pre>';print_r($createOrderResponseData);
			//$rnrOrderResponse = unserialize($createOrderResponseData);
			$rnrOrderResponse = json_decode($createOrderResponseData, true);
			unset($rnrOrderResponse["imileAwb"]); 
			unset($rnrOrderResponse["imileInvoice"]); 
			$setOrderResponse = json_encode($rnrOrderResponse, true);
			$setOrderResponse = serialize($setOrderResponse);
			$subject = '';
			if (isset($rnrOrderResponse['status']) && $rnrOrderResponse['status'] == 200) {
				$subject = 'Sales order created in iMile for order #'.$order->getIncrementId();
				$this->_objectManager->create('Hdweb\Rfc\Helper\Data')->sendRnrEmailNotification($subject, $rnrOrderResponse);
				$order->setImileOrderResponse($setOrderResponse);
				$order->addStatusToHistory($order->getStatus(), 'iMile Order created successfully with reference iMile ExpressNo:'.$rnrOrderResponse['expressNo']);
				$order->save();
				$this->_messageManager->addSuccess(__('imile Delivery Order generated successfully.'));
				$this->_redirect('sales/order/view', array('order_id' => $order_id));
			}elseif (isset($rnrOrderResponse['status']) && $rnrOrderResponse['status'] != 200) {
				$this->_messageManager->addError(__($rnrOrderResponse['description']));
				$this->_redirect('sales/order/view', array('order_id' => $order_id));
			}else{
				$this->_messageManager->addError(__('iMile Delivery Order not generated.'));
				$this->_redirect('sales/order/view', array('order_id' => $order_id));
			}
		}else{
			$this->_messageManager->addError('Something went wrong with the iMile API!.');
			$this->_redirect('sales/order/view', array('order_id' => $order_id));
		}

    }
}