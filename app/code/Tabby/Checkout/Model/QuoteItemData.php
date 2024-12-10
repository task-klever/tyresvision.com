<?php

namespace Tabby\Checkout\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Tabby\Checkout\Api\QuoteItemDataInterface;

class QuoteItemData extends AbstractExtensibleModel implements QuoteItemDataInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartItemRepositoryInterface
     */
    protected $quoteItemRepository;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartItemRepositoryInterface $quoteItemRepository
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartItemRepositoryInterface $quoteItemRepository,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource,
            $resourceCollection, $data);

        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteItemRepository = $quoteItemRepository;
    }


    /**
     * {@inheritdoc}
     */
    public function getGuestQuoteItemData($maskedId)
    {

        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($maskedId, 'masked_id');

        return $this->getQuoteItemData($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteItemData($quoteId)
    {
        $quoteItemData = [];
        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
                $quoteItemData[$index] = $quoteItem->toArray();
                /*
                                $quoteItemData[$index]['options'] = $this->getFormattedOptionValue($quoteItem);
                                $quoteItemData[$index]['thumbnail'] = $this->imageHelper->init(
                                    $quoteItem->getProduct(),
                                    'product_thumbnail_image'
                                )->getUrl();
                                $quoteItemData[$index]['message'] = $quoteItem->getMessage();
                */
            }
        }
        return $quoteItemData;
    }
}
