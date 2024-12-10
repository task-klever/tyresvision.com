<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\GoogleShoppingFeed\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductAttributes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $attributes;

    /**
     * @var Processor
     */
    private $imageProcessor;

    /**
     * @var Repository
     */
    private $productAttributeRepository;

    /**
     * Exclude incompatible product attributes from the mapping.
     * @var array
     */
    private $excluded = [];

    /**
     * Productattributes constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Processor $imageProcessor
     * @param Repository $productAttributeRepository
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Processor $imageProcessor,
        Repository $productAttributeRepository
    ) {
        $this->attributes = $collectionFactory;
        $this->imageProcessor = $imageProcessor;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * Get options.
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function toOptionArray(): array
    {
        return $this->getAttributes();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getAttributes(): array
    {
        $attributes = $this->attributes
            ->create()
            ->addVisibleFilter()
            ->setOrder('frontend_label', 'ASC');

        $attributeArray = [];
        $productAttributes = [];

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (!in_array($attributeCode, $this->excluded)) {
                $attributeArray[] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $attributeCode,
                ];
            }
        }

        $attributeArray = array_merge([['label' => 'Product ID', 'value' => 'id']], $attributeArray);

        $productAttributes[] = ['label' => 'Product Url', 'value' => 'product_url'];
        $productAttributes[] = ['label' => 'Final Price', 'value' => 'final_price'];
        $productAttributes[] = ['label' => 'Stock Status', 'value' => 'quantity_and_stock_status'];
        $productAttributes[] = ['label' => 'Product type / Full category path [product_type]', 'value' => 'product_type'];
        $productAttributes[] = ['label' => 'Google product category [google_product_category]', 'value' => 'google_product_category'];
        $productAttributes[] = ['label' => 'GTIN Based on the Product ID', 'value' => 'dynamic_gtin'];

        $mediaAttributes = $this->imageProcessor->getMediaAttributeCodes();
        $imageAttributes = [];
        foreach ($mediaAttributes as $mediaAttributeCode){
            $mediaAttribute = $this->productAttributeRepository->get($mediaAttributeCode);
            $mediaAttributeTitle = $mediaAttribute->getFrontendLabel() . ' Image Link';
            $imageAttributes[] = ['label' => $mediaAttributeTitle, 'value' => $mediaAttributeCode];
        }

        $attributesList =
            [
                [
                    'label' => __('Empty'),
                    'value' => '0',
                ],
                [
                    'label' => __('Manual value'),
                    'value' => '1',
                ],
                [
                    'label' => 'Dynamic Attributes',
                    'value' => $productAttributes
                ],
                [
                    'label' => 'Image Attributes',
                    'value' => $imageAttributes
                ]
            ];

        return array_merge($attributesList, $attributeArray);
    }
}
