<?php

namespace Hdweb\Purchaseorder\Plugin;

class PluginBeforeView
{
    protected $_storeManager;
    protected $_order;
    protected $_backendUrl;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->_storeManager = $storeManager;
        $this->_order        = $order;
        $this->_backendUrl   = $backendUrl;
    }

    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $po_params    = array('order_id' => $view->getOrderId());
        $po_actionUrl = $this->_backendUrl->getUrl("purchaseorder/create/index", $po_params);
        $view->addButton(
            'generate_po',
            ['label'  => __('Generate PO'),
                'onclick' => "setLocation('{$po_actionUrl}')",
                'class'   => 'reset',
            ],
            -1
        );

        return null;
    }

}
