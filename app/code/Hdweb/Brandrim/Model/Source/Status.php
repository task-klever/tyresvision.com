<?php

namespace Hdweb\Brandrim\Model\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    // protected $item;
 
    // public function __construct(\IWD\StoreLocator\Model\Item $item)
    // {
    //     $this->item = $item;
    // }
 
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
 
    public static function getOptionArray()
    {
        return [1 => __('Enabled'), 2 => __('Disabled')];
    }
}