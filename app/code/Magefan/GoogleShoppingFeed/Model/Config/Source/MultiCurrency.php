<?php
/**
     * Copyright © Magefan (support@magefan.com). All rights reserved.
     * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
     */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Model\Config\Source;

class MultiCurrency implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => 0, 'label' => __('Default Currency')],
            ['value' => 1, 'label' => __('All Currencies by Store')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}



