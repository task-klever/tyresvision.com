<?php

namespace Hdweb\Brandrim\Model\Source;

class Stores implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Retrieve status options array.
     *
     * @return array
     */
    protected $storelocator;
   
    public function __construct(
       \Ecomteck\StoreLocator\Model\StoresFactory $storelocator
    ) {
        $this->storelocator = $storelocator;
    
    }

    public function toOptionArray()
    {
        $allstores = $this->storelocator->create()->getCollection();
        $arr = [];
        foreach ($allstores as $option) {
        
                $arr[] =  ['value' => $option->getId(), 'label' => $option->getName()];
        
        }

        return $arr;
    }
}