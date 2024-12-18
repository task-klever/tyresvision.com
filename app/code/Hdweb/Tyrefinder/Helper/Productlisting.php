<?php
namespace Hdweb\Tyrefinder\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Productlisting extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $_scopeConfig;
    protected $_pricing;
    protected $brandFactory;
    const SETFOUR = 4;
    const SETTWO= 2;
    const SETONE= 1;    
    protected $ruleFactory;  
    protected $_objectManager;  
    protected $datetime;  
    protected $_filesystem;
    protected $customerSession;
    protected $groupRepository;
    protected $productModel;
    

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\Helper\Data $pricing,
        \Mageplaza\Shopbybrand\Model\BrandFactory $brandFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Catalog\Model\Product $productModel
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig  = $scopeConfig;
        $this->_pricing      = $pricing;
        $this->_brandFactory = $brandFactory;
        $this->ruleFactory = $ruleFactory;
        $this->_objectManager = $objectManager;
        $this->datetime = $datetime;
        $this->_filesystem = $filesystem;
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->productModel = $productModel;
        parent::__construct($context);
    }

    public function getTyreSize($_product, $long = null)
    {
        $productAttributes = '';

        $width       = $this->getAttributeValue($_product, 'width');
        $height      = $this->getAttributeValue($_product, 'height');
        $rim         = $this->getAttributeValue($_product, 'rim');
        $load_index  = $_product->getLoadIndex();
        $speed_index = $this->getAttributeValue($_product, 'speed_index');
        $parstCategory = $this->getAttributeValue($_product, 'parts_category');
        $tyresize    = "";
		if($parstCategory != 'Wheels'){
			if (isset($width) && !empty($width)) {
				if($height=='' || $height=='None') {
					$tyresize = $width . " R" .  $rim . " " . $load_index . $speed_index;
				}else{
					$tyresize = $width . '/' . $height . " R" .  $rim . " " . $load_index . $speed_index;                
				}
			}
		}else{
			$color = $this->getAttributeValue($_product, 'color');
			$pcd = $this->getAttributeValue($_product, 'pcd');
			if (isset($color) && !empty($color)) {
				$tyresize = $color . " " . $pcd;
			}
		}
        

        return $tyresize;
    }

    public function getAttributeValue($_product, $_attribute)
    {
        $_attributeId = $_product->getData($_attribute);
        $attr         = $_product->getResource()->getAttribute($_attribute);
        if ($attr->usesSource()) {
            $attributeValue = $attr->getSource()->getOptionText($_attributeId);
        }
        return $attributeValue;
    }

    public function getSet1price($_product)
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $productObject = $this->productModel->load($_product->getId());
                $final_price = $productObject->getTierPrice(1, $customerGroupId);
                $set1price   = $final_price * self::SETONE;
            }else{
                $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
                $set1price   = $final_price * self::SETONE;
                $set1price   = $set1price + ($set1price * 0.05);
            }
                
        }else{
            $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
            $set1price   = $final_price * self::SETONE;
            $set1price   = $set1price + ($set1price * 0.05);
        }
        $set1price   = $this->_pricing->currency($set1price, true, false);
        return $set1price;
    }

    public function getSet1OnlyPrice($_product)
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $productObject = $this->productModel->load($_product->getId());
                $final_price = $productObject->getTierPrice(1, $customerGroupId);
                $set1price   = $final_price * self::SETONE;
            }else{
                $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
                $set1price   = $final_price * self::SETONE;
                $set1price   = $set1price + ($set1price * 0.05);
            }
                
        }else{
            $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
            $set1price   = $final_price * self::SETONE;
            $set1price   = $set1price + ($set1price * 0.05);
        }
        
        return $set1price;
    }

    public function getSet2price($_product)
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $productObject = $this->productModel->load($_product->getId());
                $final_price = $productObject->getTierPrice(1, $customerGroupId);
                $set2price   = $final_price * self::SETTWO;
        
            }else{
                $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
                $set2price   = $final_price * self::SETTWO;
                $set2price   = $set2price + ($set2price * 0.05);
            }
                
        }else{
            $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
            $set2price   = $final_price * self::SETTWO;
            $set2price   = $set2price + ($set2price * 0.05);
        }
        
        $set2price   = $this->_pricing->currency($set2price, true, false);
        return $set2price;
    }

    public function getSet4price($_product)
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $productObject = $this->productModel->load($_product->getId());
                $final_price = $productObject->getTierPrice(1, $customerGroupId);
                $set4price   = $final_price * self::SETFOUR;
            }else{
                $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
                $set4price   = $final_price * self::SETFOUR;
                $set4price   = $set4price + ($set4price * 0.05);
            }
                
        }else{
            $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
            $set4price   = $final_price * self::SETFOUR;
            $set4price   = $set4price + ($set4price * 0.05);
        }
        
        
        $rulesId=$this->isAnyRuleExist($_product->getId());
        if(count($rulesId) > 0 ){
            if($rulesId[3] ==  4){
               $discountPercentage=$rulesId[2] / 100; 
               $set4price   = $set4price - ($set4price * $discountPercentage);
            }
        }

        $set4price   = $this->_pricing->currency($set4price, true, false);

        return $set4price;
    }

    public function getVehcileImageUrl($_product, $vehicle_model)
    {
        $vehicle_model_value = $this->getAttributeValue($_product, $vehicle_model);
        $vehicle_model_value = strtolower($vehicle_model_value) . '-icon.svg';
        $imagepath           = 'vehicle_image/' . $vehicle_model_value;
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $imagepath;
    }

    public function getBrandDetails($manufacturerId)
    {
        //$brands = $this->_brandFactory->create()->load($manufacturerId, 'option_id');
		$brand = $this->_objectManager->get('MGS\Brand\Model\Brand');
		$brands = $brand->getCollection()->addFieldToFilter('option_id', ['eq' => $manufacturerId]);
		$brands->getFirstItem();
		//echo '<pre>';print_r($brands);
		$brandArray = array();
		foreach($brands as $brandData){
			$brandArray = $brandData;
		}
		//echo '<pre>';print_r($brandArray->getData());
        return $brandArray;
    }

    public function getBrandImageUrl($brand)
    {
		//echo '<pre>';print_r($brand->getData());
        if ($brand->getImage()) {
            $image = $brand->getImage();
            return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $image;
        } else {
            return false;
        }
    }

     public function isAnyRuleExist($products_id){
        
        $objectManager = $this->_objectManager;    
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
        $currentStore = $storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $mediapathofferimage= $mediaUrl."salesrule/offerimage/";

        //$_rules = $this->ruleFactory->create()->getCollection();
        $_rules = $this->ruleFactory->create()->getCollection()->addFieldToFilter('coupon_type',1)->addFieldToFilter('is_active', 1);
        $_rules->getSelect()->order('sort_order DESC');
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $_rules->addCustomerGroupFilter($customerGroupId);
            }
        }
        
        $_currentTime = strtotime($this->datetime->date());
        
        $imageColor = array();
        foreach($_rules as $rule){
            if (!$this->customerSession->isLoggedIn()){
                /* $customerGroupId = $this->customerSession->getCustomerGroupId();
                $customerGroup = $this->groupRepository->getById($customerGroupId);
                $customerGroupCode = $customerGroup->getCode();
                if($customerGroupCode != 'Wholesale'){ */
                    $customerGroupIds = $rule->getCustomerGroupIds();
                    if (count($customerGroupIds) === 1) {
                        foreach($customerGroupIds as $customerGroupId){
                            $customerGroup = $this->groupRepository->getById($customerGroupId);
                            $customerGroupCode = $customerGroup->getCode();
                            if($customerGroupCode == 'Wholesale'){
                                continue 2;
                            }
                        }
                    }
                /* } */
            }else{
                $customerGroupId = $this->customerSession->getCustomerGroupId();
                $customerGroup = $this->groupRepository->getById($customerGroupId);
                $customerGroupCode = $customerGroup->getCode();
                if($customerGroupCode != 'Wholesale'){
                    $customerGroupIds = $rule->getCustomerGroupIds();
                    if (count($customerGroupIds) === 1) {
                        foreach($customerGroupIds as $customerGroupId){
                            $customerGroup = $this->groupRepository->getById($customerGroupId);
                            $customerGroupCode = $customerGroup->getCode();
                            if($customerGroupCode == 'Wholesale'){
                                continue 2;
                            }
                        }
                    }
                }
            }
            
            $fromDate = $rule->getFromDate();
            $toDate = $rule->getToDate();
            if (isset($fromDate) && $_currentTime >= strtotime($fromDate)) {
                if (isset($toDate)) {
                    if (strtotime($toDate) >= $_currentTime) {
                        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($products_id);
                        $item = $objectManager->create('Magento\Catalog\Model\Product');
                        $item->setProduct($product);
                        
                        $validate = $rule->getActions()->validate($item);
                        if($validate){
                             $imageColor[0]= $mediapathofferimage.$rule->getRuleBanner();
                             $imageColor[1]= $rule->getColorText();
                             $imageColor[2]= $rule->getDiscountAmount();
                             $imageColor[3]= $rule->getDiscountStep();
                        }
                    }
                }
            }
        }

   
      return $imageColor;

    }

      public function getSet1priceWithoutCurrency($_product)
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $productObject = $this->productModel->load($_product->getId());
                $final_price = $productObject->getTierPrice(1, $customerGroupId);
                $set1price   = $final_price * self::SETONE;
            }else{
                $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
                $set1price   = $final_price * self::SETONE;
                $set1price   = $set1price + ($set1price * 0.05);
            }
        }else{
            $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
            $set1price   = $final_price * self::SETONE;
            $set1price   = $set1price + ($set1price * 0.05);
        }
        
        //$set1price   = $this->_pricing->currency($set1price, true, false);
        return $set1price;
    }

    public function getRegularPrice($_product)
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
                $set1price   = $regularPrice * self::SETONE;
            }else{
                $regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
                $set1price   = $regularPrice * self::SETONE;
                $set1price   = $set1price + ($set1price * 0.05);
            }
        }else{
            $regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
            $set1price   = $regularPrice * self::SETONE;
            $set1price   = $set1price + ($set1price * 0.05);
        }
        
        $set1price   = $this->_pricing->currency($set1price, true, false);

        return $set1price;
    }

    public function getRegularOnlyPrice($_product)
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                $regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
                $set1price   = $regularPrice * self::SETONE;
            }else{
                $regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
                $set1price   = $regularPrice * self::SETONE;
                $set1price   = $set1price + ($set1price * 0.05);
            }
        }else{
            $regularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
            $set1price   = $regularPrice * self::SETONE;
            $set1price   = $set1price + ($set1price * 0.05);
        }

        return $set1price;
    }

    public function isWholeSaleCustomer()
    {
        if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
