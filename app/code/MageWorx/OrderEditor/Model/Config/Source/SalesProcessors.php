<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\OrderEditor\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SalesProcessors implements OptionSourceInterface
{
    /**
     * @param array $salesProcessors
     */
    public function __construct(
        array $salesProcessors = []
    ) {
        $this->salesProcessors = $salesProcessors;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->salesProcessors; /*[
            [
                'value' => self::MODE_UPDATE_NOTHING,
                'label' => __('Do not touch')
            ], [
                'value' => self::MODE_UPDATE_ADD,
                'label' => __('Add new shipment')
            ], [
                'value' => self::MODE_UPDATE_REBUILD,
                'label' => __('Delete shipment(s) and create new')
            ],
        ];*/
    }
}
