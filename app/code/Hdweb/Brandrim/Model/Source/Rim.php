<?php

namespace Hdweb\Brandrim\Model\Source;

class Rim implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Retrieve status options array.
     *
     * @return array
     */
    protected $eavConfig;
   
    public function __construct(
       \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;
    
    }

    public function toOptionArray()
    {
        $attributeCode = "rim";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] =  ['value' => $option['value'], 'label' => $option['label']];
            }
        }

        return $arr;
    }
}