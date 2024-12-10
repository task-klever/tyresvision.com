<?php

namespace Hdweb\Salesperson\Controller\Adminhtml\Order;
use Magento\Store\Model\ScopeInterface;

class Assignsalesperson extends \Magento\Backend\App\Action
{
    protected $_order;
    protected $scopeConfig ;
    protected $addressRenderer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
          \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
        )
    {
            parent::__construct($context);
            $this->_order = $order; 
            $this->scopeConfig  = $scopeConfig;
            $this->addressRenderer = $addressRenderer;
    }
    public function execute()
    {

        $order_id = $this->getRequest()->getParam('order_id');
        $salesid = $this->getRequest()->getParam('salesid');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$user = $objectManager->create('Magento\User\Model\User');
		$userInfo = $user->load($salesid);
		$userErpExecutiveCode = $userInfo->getUserErpExecutiveCode();
        $order = $this->_order->load($order_id);
               
        // // $this->orderSender->send($order, true);
              
		$order->setSalesPersonId($salesid);
		if($userErpExecutiveCode != ''){
			$order->setErpExecutiveCode($userErpExecutiveCode);
		}
		$order->save();
		$this->messageManager->addSuccess(__('Salesperson has been assigned successfully.'));
		$this->_redirect('sales/order/view', array('order_id' => $order_id)); 
      

    }
    
}