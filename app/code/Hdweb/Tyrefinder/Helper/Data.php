<?php

namespace Hdweb\Tyrefinder\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const WHEEL_SEARCH_APIKEY = '283e0448b0f4df5d188af4fe457b1901'; //Wheel Size API KEY
    const CAR_TYRE_CATEGORY_ID  = 'hdweb/general/car_tyre_category_id';
    const MOTORCYCLE_TYRE_CATEGORY_ID  = 'hdweb/general/motorcycle_tyre_category_id';
    const OFFSET_TYRE_CATEGORY_ID  = 'hdweb/general/offset_tyre_category_id';
    protected $_storeManager;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig  = $scopeConfig;
        parent::__construct($context);
    }

    public function getBaseUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        return $baseUrl;
    }

    public function getStorepickupUrl()
    {
        $baseUrl = $this->getBaseUrl() . 'storepickup?ref=cart';
        return $baseUrl;
    }

    public function redirectCartToStorepickup()
    {
        $cartToStorepickup = $this->scopeConfig->getValue('carttostorepickup/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $cartToStorepickup;
    }

    public function getCarTyCatId()
    {
        return (int)$this->scopeConfig->getValue(self::CAR_TYRE_CATEGORY_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMotorcycleTyCatId()
    {
        return (int)$this->scopeConfig->getValue(self::MOTORCYCLE_TYRE_CATEGORY_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getOffsetTyCatId()
    {
        return (int)$this->scopeConfig->getValue(self::OFFSET_TYRE_CATEGORY_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
