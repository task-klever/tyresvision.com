<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;
use Magento\Store\Model\ScopeInterface;

class Updateinstaller extends \Magento\Backend\App\Action
{
    protected $_order;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        )
    {
            parent::__construct($context);
            $this->_order = $order; 
            $this->_scopeConfig = $scopeConfig;
    }
    public function execute()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $installer_id = $this->getRequest()->getParam('installer');

        if(isset($installer_id) && !empty($installer_id)) {
            $order = $this->_order->load($order_id);        
            $order->setPickupStore($installer_id);
            $order->addStatusHistoryComment('Edit Installer - Installer changed successfully.');
            $order->save();
        }

        $this->messageManager->addSuccess(__('Installer changed successfully.'));
        $this->_redirect('sales/order/view', array('order_id' => $order_id)); 
    }
}