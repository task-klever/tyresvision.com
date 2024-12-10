<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Items\NewItem;

class Bundle extends AbstractType
{
    /**
     * @return bool
     */
    public function hasStockQty()
    {
        return true;
    }
}
