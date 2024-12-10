<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model;

use Exception;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Tax as OrderTax;
use MageWorx\OrderEditor\Api\ChangeLoggerInterface;
use MageWorx\OrderEditor\Api\TaxManagerInterface;
use MageWorx\OrderEditor\Helper\Data as Helper;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Model\Order\Tax\Item as OrderEditorTaxItem;

/**
 * Class Shipping
 */
class Shipping extends AbstractModel
{
    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $shippingMethod;

    /**
     * @var string
     */
    protected $shippingDescription;

    /**
     * @var float
     */
    protected $shippingPrice;

    /**
     * @var float
     */
    protected $shippingPriceInclTax;

    /**
     * @var float
     */
    protected $taxPercent;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var null
     */
    protected $rate;

    /**
     * @var Helper
     */
    protected $helperData;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var TaxManagerInterface
     */
    protected $taxManager;

    /**
     * Shipping constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Helper $helperData
     * @param Order $order
     * @param Quote $quote
     * @param DirectoryHelper $directoryHelper
     * @param MessageManagerInterface $messageManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Helper $helperData,
        Quote $quote,
        DirectoryHelper $directoryHelper,
        MessageManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        TaxManagerInterface $taxManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->quote           = $quote;
        $this->directoryHelper = $directoryHelper;
        $this->helperData      = $helperData;
        $this->messageManager  = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->taxManager      = $taxManager;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId(int $orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get current order
     *
     * @return \MageWorx\OrderEditor\Model\Order
     * @throws NoSuchEntityException
     */
    public function getOrder(): \MageWorx\OrderEditor\Model\Order
    {
        if ($this->order === null) {
            if ($this->getOrderId()) {
                $this->order = $this->orderRepository->getById($this->getOrderId());
            } else {
                $this->order = $this->helperData->getOrder();
            }
        }

        return $this->order;
    }

    /**
     * @return CartInterface|Quote
     */
    public function getQuote()
    {
        try {
            return $this->helperData->getQuote();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to load Quote. Original error message: %1', $e->getMessage())
            );

            return null;
        }
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod(string $shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getShippingDescription()
    {
        return $this->shippingDescription;
    }

    /**
     * @param string $shippingDescription
     * @return $this
     */
    public function setShippingDescription(string $shippingDescription)
    {
        $this->shippingDescription = $shippingDescription;

        return $this;
    }

    /**
     * @param float $shippingPrice
     * @return $this
     */
    public function setShippingPrice(float $shippingPrice)
    {
        $this->shippingPrice = $shippingPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getShippingPrice(): float
    {
        return $this->shippingPrice;
    }

    /**
     * @param float $shippingPriceInclTax
     * @return $this
     */
    public function setShippingPriceInclTax(float $shippingPriceInclTax)
    {
        $this->shippingPriceInclTax = $shippingPriceInclTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getShippingPriceInclTax()
    {
        return $this->shippingPriceInclTax;
    }

    /**
     * @param float $taxPercent
     * @return $this
     */
    public function setTaxPercent(float $taxPercent)
    {
        $this->taxPercent = $taxPercent;

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxPercent(): float
    {
        return $this->taxPercent;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setDiscountAmount(float $amount)
    {
        return $this->setData('discount_amount', (float)$amount);
    }

    /**
     * @return float
     */
    public function getDiscountAmount(): float
    {
        return (float)$this->getData('discount_amount');
    }

    /**
     * Set tax rates
     *
     * @param array $rates
     * @return Shipping
     */
    public function setTaxRates(array $rates = [])
    {
        return $this->setData('tax_rates', $rates);
    }

    /**
     * Get applied tax rates
     *
     * @return array
     */
    public function getTaxRates(): array
    {
        $rates = $this->getData('tax_rates');
        if (empty($rates)) {
            $rates = [];
        }

        return $rates;
    }

    /**
     * @param array $params
     * @return void
     */
    public function initParams(array $params)
    {
        if (isset($params['order_id'])) {
            $this->setOrderId((int)$params['order_id']);
        }
        if (isset($params['shipping_method'])) {
            $this->setShippingMethod((string)$params['shipping_method']);
        }
        if (isset($params['price_excl_tax'])) {
            $this->setShippingPrice((float)$params['price_excl_tax']);
        }
        if (isset($params['price_incl_tax'])) {
            $this->setShippingPriceInclTax((float)$params['price_incl_tax']);
        }
        if (isset($params['tax_percent'])) {
            $this->setTaxPercent((float)$params['tax_percent']);
        }
        if (isset($params['description'])) {
            $this->setShippingDescription((string)$params['description']);
        }
        if (isset($params['discount_amount'])) {
            $this->setDiscountAmount((float)$params['discount_amount']);
        }

        $this->setTaxRates($params['tax_rates'] ?? []);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateShippingMethod()
    {
        $order = $this->getOrder();

        $shippingAmount         = $this->getShippingPrice();
        $shippingInclTax        = $this->getShippingPriceInclTax();
        $shippingTaxAmount      = $shippingInclTax - $shippingAmount;
        $shippingDiscountAmount = $this->getDiscountAmount();

        /* convert currency */
        $baseCurrencyCode  = $order->getBaseCurrencyCode();
        $orderCurrencyCode = $order->getOrderCurrencyCode();

        if ($baseCurrencyCode === $orderCurrencyCode) {
            $baseShippingAmount         = $shippingAmount;
            $baseShippingInclTax        = $shippingInclTax;
            $baseShippingTaxAmount      = $shippingTaxAmount;
            $baseShippingDiscountAmount = $shippingDiscountAmount;
        } else {
            $baseShippingAmount         = $this->convertAmountToBaseCurrency(
                $shippingAmount
            );
            $baseShippingInclTax        = $this->convertAmountToBaseCurrency(
                $shippingInclTax
            );
            $baseShippingTaxAmount      = $this->convertAmountToBaseCurrency(
                $shippingTaxAmount
            );
            $baseShippingDiscountAmount = $this->convertAmountToBaseCurrency(
                $shippingDiscountAmount
            );
        }

        // Recalculate discount amount based on shipping discount amount
        $orderDiscountAmountOld        = abs((float)$order->getDiscountAmount());
        $orderBaseDiscountAmountOld    = abs((float)$order->getBaseDiscountAmount());
        $shippingDiscountAmountOld     = abs((float)$order->getShippingDiscountAmount());
        $shippingBaseDiscountAmountOld = abs((float)$order->getBaseShippingDiscountAmount());

        if ($shippingDiscountAmountOld != $shippingDiscountAmount) {
            $orderDiscountAmountNew     = $orderDiscountAmountOld
                - $shippingDiscountAmountOld
                + $shippingDiscountAmount;
            $orderBaseDiscountAmountOld = $orderBaseDiscountAmountOld
                - $shippingBaseDiscountAmountOld
                + $baseShippingDiscountAmount;

            $order->setDiscountAmount($orderDiscountAmountNew)
                  ->setBaseDiscountAmount($orderBaseDiscountAmountOld)
                  ->setShippingDiscountAmount($shippingDiscountAmount)
                  ->setBaseShippingDiscountAmount($baseShippingDiscountAmount);
        }

        if ($order->getShippingMethod() != $this->getShippingMethod()) {
            $this->_eventManager->dispatch(
                'mageworx_log_changes_on_order_edit',
                [
                    ChangeLoggerInterface::SIMPLE_MESSAGE_KEY => __(
                        'Shipping method has been changed from <b>%1</b> to <b>%2</b>',
                        $order->getShippingDescription(),
                        $this->getShippingDescription()
                    )
                ]
            );
        } elseif ($order->getShippingDescription() != $this->getShippingDescription()) {
            $this->_eventManager->dispatch(
                'mageworx_log_changes_on_order_edit',
                [
                    ChangeLoggerInterface::SIMPLE_MESSAGE_KEY => __(
                        'Shipping method title has been changed from <b>%1</b> to <b>%2</b>',
                        $order->getShippingDescription(),
                        $this->getShippingDescription()
                    )
                ]
            );
        }

        if ($order->getShippingAmount() != $shippingAmount) {
            $this->_eventManager->dispatch(
                'mageworx_log_changes_on_order_edit',
                [
                    ChangeLoggerInterface::SIMPLE_MESSAGE_KEY => __(
                        'Shipping Price has been changed from <b>%1</b> to <b>%2</b>',
                        $order->formatPriceTxt($order->getShippingAmount()),
                        $order->formatPriceTxt($shippingAmount)
                    )
                ]
            );
        }

        $changesInShipping = [
            'base_amount'          => $baseShippingAmount - $order->getBaseShippingAmount(),
            'base_amount_incl_tax' => $baseShippingInclTax - $order->getBaseShippingInclTax(),
            'base_tax'             => $baseShippingTaxAmount - $order->getBaseShippingTaxAmount()
        ];

        $order->addShippingChanges($changesInShipping);

        $order->setShippingDescription($this->getShippingDescription())
              ->setData('shipping_method', $this->getShippingMethod())
              ->setShippingAmount($shippingAmount)
              ->setBaseShippingAmount($baseShippingAmount)
              ->setShippingInclTax($shippingInclTax)
              ->setBaseShippingInclTax($baseShippingInclTax)
              ->setShippingTaxAmount($shippingTaxAmount)
              ->setBaseShippingTaxAmount($baseShippingTaxAmount);

        $order->calculateGrandTotal();
        $order->updatePayment();

        $this->orderRepository->save($order);
        $this->updateTaxTables();

        $this->_eventManager->dispatch(
            'mageworx_order_updated',
            [
                'action' => \MageWorx\OrderEditor\Api\WebhookProcessorInterface::ACTION_UPDATE_ORDER_SHIPPING_METHOD,
                'object' => $order,
                'initial_params' => [
                    'shipping_method' => $this->getShippingMethod()
                ]
            ]
        );

        $this->_eventManager->dispatch(
            'mageworx_save_logged_changes_for_order',
            [
                'order_id'        => $order->getId(),
                'notify_customer' => false
            ]
        );
    }

    /**
     * Converts order amount to base currency amount.
     * We must use own method because magento have only base and default currency rates, but not an all available rates.
     *
     * @param float $amount
     * @return float
     */
    public function convertAmountToBaseCurrency(float $amount): float
    {
        try {
            $order = $this->getOrder();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return 0;
        }

        $orderCurrencyCode = $order->getOrderCurrencyCode();
        $baseCurrencyCode  = $order->getBaseCurrencyCode();
        if ($orderCurrencyCode == $baseCurrencyCode) {
            return $amount;
        }

        $rate = $order->getOrderCurrency()->getAnyRate($baseCurrencyCode);
        if ($rate < 0.0001) {
            $this->messageManager->addErrorMessage(
                __(
                    'Currency Rate for %1 must be greater than 0',
                    $baseCurrencyCode . '/' . $orderCurrencyCode
                )
            );

            return 0;
        }

        $amount = $amount * $rate;

        return $amount;
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function reloadShippingRates()
    {
        if (!$this->getQuote()) {
            $this->getOrder()->syncQuote();
        }
    }

    /**
     * @param array $orderTaxIds
     * @return OrderEditorTaxItem[]
     */
    protected function getShippingTaxItems(array $orderTaxIds): array
    {
        $taxItemsCollection = $this->taxManager->getOrderTaxItemsCollection();
        $taxItemsCollection->addFieldToSelect('*');
        $taxItemsCollection->addFilter('taxable_item_type', 'shipping');
        $taxItemsCollection->addFieldToFilter('tax_id', ['in' => $orderTaxIds]);
        /** @var OrderEditorTaxItem[] $shippingTaxItems */
        $shippingTaxItems = $taxItemsCollection->getItems();

        $shippingTaxItemsByCode = [];

        try {
            $order = $this->getOrder();
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('Exception during tax calculations: no order selected'));

            return [];
        }

        $orderTaxes = $this->taxManager->getOrderTaxes($order->getId());

        // Save tax items in array by tax code to make search item by code not so complicated each time when we need it.
        foreach ($shippingTaxItems as $shippingTaxItem) {
            $taxId = $shippingTaxItem->getData('tax_id');
            $tax   = $orderTaxes[$taxId];
            $code  = $tax->getData('code');

            $shippingTaxItemsByCode[$code] = $shippingTaxItem;
        }

        return $shippingTaxItemsByCode;
    }

    /**
     * Save new tax rates in corresponding tables.
     * Clear old values.
     *
     * @throws NoSuchEntityException
     */
    protected function updateTaxTables()
    {
        $order      = $this->getOrder();
        $taxRates   = $this->getTaxRates();
        $orderTaxes = $this->taxManager->getOrderTaxes($order->getId());

        $orderTaxIds      = [];
        $orderTaxesByCode = [];

        foreach ($orderTaxes as $orderTax) {
            $orderTaxIds[]              = $orderTax->getData('tax_id');
            $taxCode                    = $orderTax->getData('code');
            $orderTaxesByCode[$taxCode] = $orderTax;
        }
        // Here we got all taxes of the order and its ids
        // Now we need all tax items for the order

        // Save tax items in array by tax code to make search item by code not so complicated each time when we need it.
        $shippingTaxItemsByCode = $this->getShippingTaxItems($orderTaxIds);

        // Now we check all rates from request: which exists, which new etc.
        foreach ($taxRates as $rate) {
            if ($rate instanceof \MageWorx\OrderEditor\Api\Data\OrderManager\TaxRateDataInterface) {
                $rate = $rate->toArray();
            }
            $this->processTaxRate($rate, $shippingTaxItemsByCode, $orderTaxesByCode, $order);
        }

        $this->deleteNonExistingTaxItems($shippingTaxItemsByCode);
    }

    /**
     * Process tax rate and save changes in the tax and tax item
     *
     * @param array $rate
     * @param array $shippingTaxItemsByCode
     * @param array $orderTaxesByCode
     * @param Order $order
     * @return void
     * @throws NoSuchEntityException
     */
    private function processTaxRate(
        array $rate,
        array $shippingTaxItemsByCode,
        array $orderTaxesByCode,
        Order $order
    ) {
        if ($rate instanceof \MageWorx\OrderEditor\Api\Data\OrderManager\TaxRateDataInterface) {
            $rate = $rate->toArray();
        }

        $rateCode           = (string)$rate['code'];
        $ratePercent        = (float)$rate['percent'];
        $rateAmount         = $this->getShippingPrice() * ((float)$rate['percent'] / 100);
        $rateBaseAmount     = $this->currencyConvertToBaseCurrency($rateAmount);
        $rateBaseRealAmount = $rateBaseAmount;

        /** @var OrderEditorTaxItem $taxItem */
        $taxItem = isset($shippingTaxItemsByCode[$rateCode]) ?
            $shippingTaxItemsByCode[$rateCode] :
            $this->taxManager->getOrderTaxItemsCollection()->getNewEmptyItem();

        /** @var \Magento\Sales\Model\Order\Tax $tax */
        if ($taxItem->isObjectNew()) {
            // Tax item does not exists, but similar tax may be exist
            if (isset($orderTaxesByCode[$rateCode])) {
                // Update existing tax record ...
                $tax = $orderTaxesByCode[$rateCode];
            } else {
                // ... or create new tax record.
                $tax = $this->taxManager->getOrderTaxCollection()->getNewEmptyItem();
            }

            $taxData = [
                'order_id'         => $order->getId(),
                'code'             => $rateCode,
                'title'            => $rateCode,
                'percent'          => $ratePercent,
                'amount'           => $tax->getAmount() + $rateAmount,
                'priority'         => (int)$tax->getPriority(),
                'position'         => (int)$tax->getPosition(),
                'base_amount'      => $tax->getBaseAmount() + $rateBaseAmount,
                'process'          => (int)$tax->getProcess(),
                'base_real_amount' => $tax->getBaseRealAmount() + $rateBaseRealAmount,
            ];
            $tax->addData($taxData);
            $this->taxManager->saveOrderTax($tax);

            $taxItemData = [
                'tax_percent'       => $ratePercent,
                'amount'            => $rateAmount,
                'real_amount'       => $rateAmount,
                'base_amount'       => $rateBaseAmount,
                'real_base_amount'  => $rateBaseRealAmount,
                'tax_id'            => $tax->getTaxId(),
                'taxable_item_type' => 'shipping'
            ];
            $taxItem->addData($taxItemData);
            $this->taxManager->saveOrderTaxItem($taxItem);
        } else {
            // Tax item exists, similar tax must be exist
            if ((float)$taxItem->getData('tax_percent') == $ratePercent
                && abs((float)$taxItem->getData('amount') - $rateAmount) < 0.0001
            ) {
                return; // Nothing has been changed, skip saving (do not update)
            }

            // Check diff and correct tax and tax item amounts
            $tax = $orderTaxesByCode[$rateCode];

            // Remove old tax item amounts from the tax
            $this->removeTaxItemAmountFromTax($tax, $taxItem);

            // Change tax item amount from old to new values
            $taxItemData = [
                'tax_percent'      => $ratePercent,
                'amount'           => $rateAmount,
                'real_amount'      => $rateAmount,
                'base_amount'      => $rateBaseAmount,
                'real_base_amount' => $rateBaseRealAmount
            ];
            $taxItem->addData($taxItemData);
            $this->taxManager->saveOrderTaxItem($taxItem);

            // Correct tax amounts according new tax item amounts
            $this->addTaxItemAmountToTax($tax, $taxItem);
        }
    }

    /**
     * Remove old tax item amounts from the tax
     *
     * @param OrderTax $tax
     * @param OrderEditorTaxItem $taxItem
     */
    private function removeTaxItemAmountFromTax($tax, $taxItem)
    {
        try {
            $tax->setAmount($tax->getAmount() - $taxItem->getAmount());
            $tax->setBaseAmount($tax->getBaseAmount() - $taxItem->getBaseAmount());
            $tax->setBaseRealAmount($tax->getBaseRealAmount() - $taxItem->getRealBaseAmount());
            $this->taxManager->saveOrderTax($tax);
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage(
                __(
                    'Something goes wrong while deducting tax amounts. Original error message: %1',
                    $exception->getMessage()
                )
            );
        }
    }

    /**
     * Correct tax amounts according new tax item amounts
     *
     * @param OrderTax $tax
     * @param OrderEditorTaxItem $taxItem
     */
    private function addTaxItemAmountToTax($tax, $taxItem)
    {
        try {
            $tax->setAmount($tax->getAmount() + $taxItem->getAmount());
            $tax->setBaseAmount($tax->getBaseAmount() + $taxItem->getBaseAmount());
            $tax->setBaseRealAmount($tax->getBaseRealAmount() + $taxItem->getRealBaseAmount());
            $this->taxManager->saveOrderTax($tax);
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage(
                __(
                    'Something goes wrong while increasing tax amounts. Original error message: %1',
                    $exception->getMessage()
                )
            );
        }
    }

    /**
     * Delete removed tax items (unchecked tax rates)
     *
     * @param array $shippingTaxItems
     * @return void
     */
    protected function deleteNonExistingTaxItems(array $shippingTaxItems = [])
    {
        $taxItemsToDelete = [];
        $ratesCodes       = [];

        $taxRates = $this->getTaxRates();
        foreach ($taxRates as $rate) {
            if ($rate instanceof \MageWorx\OrderEditor\Api\Data\OrderManager\TaxRateDataInterface) {
                $rate = $rate->toArray();
            }
            $ratesCodes[] = $rate['code'];
        }

        try {
            $order = $this->getOrder();
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('Exception during tax calculations: no order selected'));

            return;
        }

        $orderTaxes = $this->taxManager->getOrderTaxes($order->getId());

        foreach ($shippingTaxItems as $shippingTaxItem) {
            $taxId = $shippingTaxItem->getData('tax_id');
            $tax   = $orderTaxes[$taxId];
            $code  = $tax->getData('code');

            if (!in_array($code, $ratesCodes)) {
                $taxItemsToDelete[] = $shippingTaxItem;
            }
        }

        if (!empty($taxItemsToDelete)) {
            try {
                $this->taxManager->deleteTaxItems($taxItemsToDelete);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Unable to delete old tax. Original error message: %1',
                        $exception->getMessage()
                    )
                );
            }
        }
    }

    /**
     * Convert store amount to base amount
     *
     * @param float $amount
     * @return float
     * @throws NoSuchEntityException
     */
    protected function currencyConvertToBaseCurrency($amount): float
    {
        $rate       = (float)$this->getOrder()->getBaseToOrderRate();
        $rate       = $rate > 0 ? $rate : 1;
        $baseAmount = $amount / $rate;

        return (float)$baseAmount;
    }
}
