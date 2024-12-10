<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Items;

use Magento\Sales\Api\Data\OrderItemInterface;
use MageWorx\OrderEditor\Model\Quote\Item;
use Magento\Backend\Block\Template;
use Magento\Downloadable\Model\Link;
use Magento\Store\Model\ScopeInterface;
use MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Items\Type\AbstractType as AbstractItemsForm;

/**
 * Class Options
 *
 * @method bool hasItemType()
 * @method string getItemType()
 */
class Options extends Template
{
    /**
     * @var OrderItemInterface|null
     */
    protected $orderItem = null;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $purchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $itemsFactory;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $downloadableLink;

    /**
     * Options constructor.
     *
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
     * @param Link $downloadableLink
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory,
        \Magento\Downloadable\Model\Link $downloadableLink,
        array $data = []
    ) {
        $this->optionFactory    = $optionFactory;
        $this->purchasedFactory = $purchasedFactory;
        $this->itemsFactory     = $itemsFactory;
        $this->downloadableLink = $downloadableLink;

        parent::__construct($context, $data);
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return $this
     */
    public function setOrderItem(OrderItemInterface $orderItem)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * @return Item
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return string[]
     */
    public function getOrderOptions(): array
    {
        $result  = [];
        $options = $this->getOrderItem()->getProductOptions();

        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['links'])) {
                $result = array_merge($result, $this->getLinksOptions($options['links']));
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }

        return $result;
    }

    /**
     * @param string[] $options
     * @return string[][]
     */
    public function getLinksOptions($options): array
    {
        $links = [];

        foreach ($options as $linkId) {
            /** @var $link \Magento\Downloadable\Model\Link */
            $link = $this->downloadableLink->getCollection()
                                           ->addTitleToResult()
                                           ->addFieldToFilter('main_table.link_id', $linkId)
                                           ->getFirstItem();

            if (empty($link)) {
                continue;
            }

            $links[] = $link->getDefaultTitle();
        }

        return [
            [
                'label' => $this->getLinksTitle(),
                'value' => implode(', ', $links)
            ]
        ];
    }

    /**
     * @return string
     */
    public function getLinksTitle()
    {
        $purchasedLinks = $this->purchasedFactory->create()->load(
            $this->getOrderItem()->getId(),
            'order_item_id'
        );

        $linkSectionTitle = $purchasedLinks->getLinkSectionTitle();

        return $linkSectionTitle ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string[] $optionInfo
     * @return string
     */
    public function getCustomizedOptionValue($optionInfo): string
    {
        // render customized option view
        $_default = $optionInfo['value'];
        if (isset($optionInfo['option_type'])) {
            try {
                return $this->optionFactory->create()
                                           ->groupFactory($optionInfo['option_type'])
                                           ->getCustomizedView($optionInfo);
            } catch (\Exception $e) {
                return $_default;
            }
        }

        return $_default;
    }

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncateString(
        $value,
        $length = 80,
        $etc = '...',
        &$remainder = '',
        $breakWords = true
    ): string {
        return $this->filterManager->truncate(
            $value,
            [
                'length'     => $length,
                'etc'        => $etc,
                'remainder'  => $remainder,
                'breakWords' => $breakWords
            ]
        );
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value): array
    {
        $remainder = '';
        $value     = $this->truncateString($value, 55, '', $remainder);
        $result    = [
            'value'     => nl2br($value),
            'remainder' => nl2br($remainder)
        ];

        return $result;
    }

    /**
     * @return string
     */
    public function getItemId(): string
    {
        $prefix = $this->getEditedItemType() == AbstractItemsForm::ITEM_TYPE_QUOTE ? Item::PREFIX_ID : '';

        return $prefix . $this->getOrderItem()->getItemId();
    }

    /**
     * @return string
     */
    public function getEditedItemType(): string
    {
        if ($this->hasItemType()) {
            return $this->getItemType();
        }

        return AbstractItemsForm::ITEM_TYPE_ORDER;
    }
}
