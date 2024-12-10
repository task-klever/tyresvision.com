<?php
namespace Hdweb\Rfc\Block\Adminhtml\OrderEdit\Tab;

/**
 * Order custom tab
 *
 */
class View extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'tab/view/rnr_order_info.phtml';

    /**
     * View constructor.
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
     * Retrieve order Rnr response
     *
     * @return string
     */
    public function getErpSalesHeaderRef()
    {
        return $this->getOrder()->getErpSalesHeaderRef();
    }
	
	public function getErpSalesOrderItem()
    {
        return $this->getOrder()->getErpSalesOrderItem();
    }
	
	/**
     * Retrieve invoice ErpOrderItemRef
     *
     * @return string
     */
    public function getErpOrderItemRef()
    {
		$erpSalesOrderItem = array();
		foreach ($this->getOrder()->getAllVisibleItems() as $item) {
			$erpSalesOrderItem[] = $item->getErpSalesOrderItem();
		}
        return $erpSalesOrderItem;
    }
	
	public function showGenerateErpButton()
    {
		$checkErpProductRef = array();
		
		foreach ($this->getOrder()->getAllVisibleItems() as $item) {
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
	
	/**
     * Retrieve product erp ref
     *
     * @return string
     */
    public function getErpProductRef()
    {
		$checkErpProductRef = array();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		foreach($this->getOrder()->getAllVisibleItems() as $item) {
			$product = $objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku',$item->getSku());
			if($product->getErpSku() != ''){
				$checkErpProductRef[] = 1;
				//echo $product->getErpProductRef().'<br/>';
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
     * Retrieve purchase order vendor code
     *
     * @return string
     */
    public function getPoVendorCode($vendor_id)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$povendorObj = $objectManager->get('Hdweb\Purchaseorder\Model\Povendor');
		$povendorCollection = $povendorObj->getCollection()->addFieldToFilter('id',$vendor_id)->getFirstItem();
		//echo '<pre>';print_r($povendorCollection->getData());
        return $povendorCollection->getCode();
    }
	
	/**
     * Retrieve order Erp Inventory Location
     *
     * @return string
     */
    public function getErpInventoryLocation()
    {
        return $this->getOrder()->getErpInventoryLocation();
    }
	
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('ERP Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('ERP Information');
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