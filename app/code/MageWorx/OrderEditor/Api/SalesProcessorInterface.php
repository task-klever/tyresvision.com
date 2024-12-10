<?php

namespace MageWorx\OrderEditor\Api;

use MageWorx\OrderEditor\Model\Order as OrderEditorOrder;

interface SalesProcessorInterface
{
    /**
     * Set order to process
     *
     * @param OrderEditorOrder $order
     * @return $this
     */
    public function setOrder(OrderEditorOrder $order): SalesProcessorInterface;

    /**
     * Get actual order
     *
     * @return OrderEditorOrder|null
     */
    public function getOrder(): ?OrderEditorOrder;

    /**
     * Update credit-memos, invoices, shipments
     *
     * @return bool
     */
    public function updateSalesObjects(): bool;
}
