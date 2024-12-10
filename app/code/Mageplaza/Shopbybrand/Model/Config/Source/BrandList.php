<?php
namespace Mageplaza\Shopbybrand\Model\Config\Source;
class BrandList implements \Magento\Framework\Option\ArrayInterface
{
	protected $eavConfig;
	public function __construct(
	    \Magento\Eav\Model\Config $eavConfig
	){
	    $this->eavConfig = $eavConfig;
	}
	public function toOptionArray()
    {

    	$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
    	$attribute = $this->eavConfig->getAttribute('catalog_product', 'brand');
		$options = $attribute->getSource()->getAllOptions();

		$optionsExists = array();
		
		foreach($options as $option) {
			
			if($option['value'] == '') continue;
		    
		    $optionsExists[] = array('label' => $option['label'], 'value' => $option['value']);
		}

		return $optionsExists;
    }
}
