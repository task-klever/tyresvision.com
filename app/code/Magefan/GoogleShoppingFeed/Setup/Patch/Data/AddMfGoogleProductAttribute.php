<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddMfGoogleProductAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    const MF_GOOGLE_PRODUCT_CATEGORY = 'mf_google_product_category';
    const MF_GOOGLE_PRODUCT_PRODUCT = 'mf_google_product_product';
    const MF_EXCLUDED_GOOGLE_FEED_CATEGORY = 'mf_exclude_google_feed_category';
    const MF_EXCLUDED_GOOGLE_FEED_PRODUCT = 'mf_exclude_google_feed_product';

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    private $collectionFactory;
    private $action;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        CollectionFactory $collectionFactory,
        Action $action
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->collectionFactory = $collectionFactory;
        $this->action = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /** Add to Category */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            self::MF_GOOGLE_PRODUCT_CATEGORY,
            [
                'type' => 'int',
                'label' => 'Google Product Category',
                'input' => 'select',
                'sort_order' => 333,
                'source' => 'Magefan\GoogleShoppingFeed\Model\Entity\Attribute\Source\GoogleCategory',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'Google Shopping Feed',
                'backend' => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            self::MF_EXCLUDED_GOOGLE_FEED_CATEGORY,
            [
                'type' => 'int',
                'label' => 'Exclude From Google Shopping Feed',
                'input' => 'select',
                'sort_order' => 400,
                'source' => 'Magefan\GoogleShoppingFeed\Model\Entity\Attribute\Source\Exclude',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'Google Shopping Feed',
                'backend' => ''
            ]
        );

        /** Add to Product */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::MF_GOOGLE_PRODUCT_PRODUCT,
            [
                'type' => 'int',
                'label' => 'Google Product Category',
                'input' => 'select',
                'sort_order' => 333,
                'source' => 'Magefan\GoogleShoppingFeed\Model\Entity\Attribute\Source\GoogleCategory',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'group' => 'Google Shopping Feed',
                'backend' => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::MF_EXCLUDED_GOOGLE_FEED_PRODUCT,
            [
                'type' => 'int',
                'label' => 'Exclude From Google Shopping Feed',
                'input' => 'select',
                'source' => 'Magefan\GoogleShoppingFeed\Model\Entity\Attribute\Source\Exclude',
                'frontend' => '',
                'required' => false,
                'backend' => '',
                'sort_order' => '30',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => '0',
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => '',
                'group' => 'Google Shopping Feed',
                'used_in_product_listing' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'option' => array('values' => array(""))
            ]
        );

        /** Assign attribute to all products */
        $productIds = $this->collectionFactory->create()->getAllIds();
        $this->action->updateAttributes($productIds, [self::MF_EXCLUDED_GOOGLE_FEED_PRODUCT => 0], 0);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Category::ENTITY, self::MF_EXCLUDED_GOOGLE_FEED_CATEGORY
        );
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY, self::MF_EXCLUDED_GOOGLE_FEED_PRODUCT
        );
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Category::ENTITY, self::MF_GOOGLE_PRODUCT_CATEGORY
        );
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY, self::MF_GOOGLE_PRODUCT_PRODUCT
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}