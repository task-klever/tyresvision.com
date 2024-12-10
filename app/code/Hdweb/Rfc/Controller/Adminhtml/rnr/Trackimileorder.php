<?php
namespace Hdweb\Rfc\Controller\Adminhtml\rnr;

class Trackimileorder extends \Magento\Backend\App\Action
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
			$orderData          	 = array(
				"sign"          	 => "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALKzHHqoHr2r1CVY",
				"signMethod"         => "simpleKey", // simpleKey or md5 if using md5 sign
				"param"    			 => array(
					"orderType"		 =>	"2",
					"language"		 =>	"2", //english
					"orderNo"      	 => $order->getIncrementId(), //$order->getIncrementId(),
				),
				"customerId"   		 => "9900011",
				"format" 			 => "json",
				"method" 			 => "trackOrderOneByOne"
			);
			$orderRequest = json_encode($orderData);
			//echo '<pre>';print_r($orderRequest);die;
			$createOrderResponseData = $this->_objectManager->create('Hdweb\Rfc\Helper\Data')->getiMileCreateOrder($orderRequest);
			//echo '<pre>';print_r($createOrderResponseData);
			//$rnrOrderResponse = unserialize($createOrderResponseData);
			$rnrOrderResponse = json_decode($createOrderResponseData, true);
			$setOrderResponse = json_encode($rnrOrderResponse, true);
			$setOrderResponse = serialize($setOrderResponse);
			
			$subject = '';
			if (isset($rnrOrderResponse['status']) && $rnrOrderResponse['status'] == 200) {
				$subject = 'Sales order tracking information in iMile for order #'.$order->getIncrementId();
				$this->_objectManager->create('Hdweb\Rfc\Helper\Data')->sendRnrEmailNotification($subject, $rnrOrderResponse);
				$order->setImileOrderTrackingResponse($setOrderResponse);
				$order->addStatusToHistory($order->getStatus(), 'iMile Order tracking with reference Order#'.$order->getIncrementId().' and billNo '.$rnrOrderResponse['locusDetailed'][0]['billNo']);
				$order->save();
				$this->_messageManager->addSuccess(__('imile Delivery Order tracked successfully.'));
				$this->_redirect('sales/order/view', array('order_id' => $order_id));
			}elseif (isset($rnrOrderResponse['status']) && $rnrOrderResponse['status'] != 200) {
				$this->_messageManager->addError(__($rnrOrderResponse['description']));
				$this->_redirect('sales/order/view', array('order_id' => $order_id));
			}else{
				$this->_messageManager->addError(__('iMile Delivery Order not available tracking.'));
				$this->_redirect('sales/order/view', array('order_id' => $order_id));
			}
		}else{
			$this->_messageManager->addError('Something went wrong with the iMile API!.');
			$this->_redirect('sales/order/view', array('order_id' => $order_id));
		}

    }
}