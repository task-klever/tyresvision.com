<?php
namespace Hdweb\Rfc\Block\Adminhtml\OrderEdit\Tab;

/**
 * Order custom tab
 *
 */
class Delivery extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'tab/view/imile_delivery.phtml';

    /**
     * Delivery constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Retrieve order iMile response
     *
     * @return string
     */
    public function getOrderiMileResponse()
    {
        return $this->getOrder()->getImileOrderResponse();
    }
	
	/**
     * Retrieve order iMile tracking response
     *
     * @return string
     */
    public function getOrderiMileTrackingResponse()
    {
        return $this->getOrder()->getImileOrderTrackingResponse();
    }
	
	/**
     * Retrieve order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }
	
	
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('iMile Delivery');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('iMile Delivery');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}