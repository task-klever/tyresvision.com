<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GoogleTagManager
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GoogleTagManager\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CatalogPrice;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Checkout\Model\Session;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\GoogleTagManager\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'googletagmanager';

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $_attributeSet;

    /**
     * @var Category
     */
    protected $_resourceCategory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;

    /**
     * @var CatalogPrice
     */
    protected $_catalogPrice;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_convert;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     * @param Registry $registry
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param Category $resourceCategory
     * @param ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param CatalogPrice $catalogPrice
     * @param Session $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        Registry $registry,
        AttributeSetRepositoryInterface $attributeSetRepository,
        Category $resourceCategory,
        ProductFactory $productFactory,
        \Magento\Catalog\Helper\Data $catalogHelper,
        CatalogPrice $catalogPrice,
        Session $checkoutSession,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_categoryFactory  = $categoryFactory;
        $this->_registry         = $registry;
        $this->_checkoutSession  = $checkoutSession;
        $this->_attributeSet     = $attributeSetRepository;
        $this->_resourceCategory = $resourceCategory;
        $this->_productFactory   = $productFactory;
        $this->_catalogHelper    = $catalogHelper;
        $this->_catalogPrice     = $catalogPrice;
        $this->_convert          = $priceCurrency;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param int $price
     *
     * @return float
     */
    public function convertPrice($price)
    {
        return $this->_convert->convert($price);
    }

    /**
     * @return Registry
     */
    public function getGtmRegistry()
    {
        return $this->_registry;
    }

    /**
     * Get GTM checkout session
     * @return Session
     */
    public function getSessionManager()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get Google Tag Manager Config
     *
     * @param $code
     * @param null $store
     *
     * @return mixed
     */
    public function getConfigGTM($code, $store = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/googletag' . $code, $store);
    }

    /**
     * Get Google Analytics Config
     *
     * @param $code
     * @param null $store
     *
     * @return mixed
     */
    public function getConfigAnalytics($code, $store = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/analyticstag' . $code, $store);
    }

    /**
     * @param $code
     * @param null $store
     *
     * @return mixed
     */
    public function getConfigPixel($code, $store = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/pixeltag' . $code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function getGTMUseIdOrSku($store = null)
    {
        return $this->getConfigGTM('use_id_or_sku', $store);
    }

    /**
     * @param $code
     * @param null $store
     *
     * @return mixed
     */
    public function getPixelUseIdOrSku($code, $store = null)
    {
        return $this->getConfigPixel('use_id_or_sku', $store);
    }

    /**
     * @param $code
     * @param null $store
     *
     * @return mixed
     */
    public function getAnalyticsUseIdOrSku($code, $store = null)
    {
        return $this->getConfigAnalytics('use_id_or_sku', $store);
    }

    /**
     * Get Store Currency Code. EG:  'currencyCode': 'EUR','USD'
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrentCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get Store ID
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Measure the additional of a product to a shopping cart.
     *
     * @param $product
     * @param $quantity
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGTMAddToCartData($product, $quantity)
    {
        $useIdOrSku = $this->getGTMUseIdOrSku($this->getStoreId());

        $productData          = [];
        $productData['id']    = $useIdOrSku ? $product->getSku() : $product->getId();
        $productData['sku']   = $product->getSku();
        $productData['name']  = $product->getName();
        $productData['price'] = $this->getPrice($product);
        if ($this->getProductBrand($product)) {
            $productData['brand'] = $this->getProductBrand($product);
        }
        if ($this->getColor($product)) {
            $productData['variant'] = $this->getColor($product);
        }
        if (!empty($this->getCategoryNameByProduct($product))) {
            $productData['category'] = $this->getCategoryNameByProduct($product);
        }
        $productData['quantity'] = $quantity;

        return $productData;
    }

    /**
     * @param Product $product
     * @param $list
     * @param $position
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getItems($product, $list, $position)
    {
        $useIdOrSku = $this->getAnalyticsUseIdOrSku($this->getStoreId());

        return [
            'id'            => $useIdOrSku ? $product->getSku() : $product->getId(),
            'name'          => $product->getName(),
            'list_name'     => $list,
            'brand'         => $this->getProductBrand($product),
            'category'      => $this->getCategoryNameByProduct($product),
            'variant'       => $this->getColor($product),
            'list_position' => $position,
            'quantity'      => 1,
            'price'         => $this->getPrice($product),
        ];
    }

    /**
     * @param Product $product
     * @param $quantity
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFBAddToCartData($product, $quantity)
    {
        $useIdOrSku = $this->getPixelUseIdOrSku($this->getStoreId());

        return [
            'id'       => $useIdOrSku ? $product->getSku() : $product->getId(),
            'name'     => $product->getName(),
            'price'    => $this->getPrice($product),
            'quantity' => $quantity
        ];
    }

    /**
     * @param Product $product
     * @param $quantity
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGAAddToCartData($product, $quantity)
    {
        $useIdOrSku = $this->getAnalyticsUseIdOrSku($this->getStoreId());

        return [
            'id'        => $useIdOrSku ? $product->getSku() : $product->getId(),
            'name'      => $product->getName(),
            'list_name' => 'Add To Cart',
            'brand'     => $this->getProductBrand($product),
            'category'  => $this->getCategoryNameByProduct($product),
            'variant'   => $this->getColor($product),
            'quantity'  => $quantity,
            'price'     => $this->getPrice($product),
        ];
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    public function getCategoryNameByProduct($product)
    {
        $categoryIds  = $product->getCategoryIds();
        $categoryName = '';
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $category     = $this->_categoryFactory->create()->load($categoryId);
                $categoryName .= '/' . $category->getName();
            }
        }

        return trim($categoryName, '/');
    }

    /**
     * @param Product $product
     * @param $quantity
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGARemoveFromCartData($product, $quantity)
    {
        $useIdOrSku = $this->getAnalyticsUseIdOrSku($this->getStoreId());

        $data = [
            'items' => [
                [
                    'id'        => $useIdOrSku ? $product->getSku() : $product->getId(),
                    'name'      => $product->getName(),
                    'list_name' => 'Remove To Cart',
                    'brand'     => $this->getProductBrand($product),
                    'category'  => $this->getCategoryNameByProduct($product),
                    'variant'   => $this->getColor($product),
                    'quantity'  => $quantity,
                    'price'     => $this->getPrice($product)
                ]
            ]
        ];

        return $data;
    }

    /**
     * @param Product $product
     *
     * @return float
     */
    public function getPrice($product)
    {
        $price = $product->getFinalPrice() ?: $product->getPrice();
        if ($product->getTypeId() === 'configurable') {
            return $price;
        }

        return $this->convertPrice($price);
    }

    /**
     * Measure the removal of a product from a shopping cart.
     *
     * @param Product $product
     * @param $quantity
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGTMRemoveFromCartData($product, $quantity)
    {
        $useIdOrSku = $this->getGTMUseIdOrSku($this->getStoreId());

        $productData          = [];
        $productData['id']    = $useIdOrSku ? $product->getSku() : $product->getId();
        $productData['sku']   = $product->getSku();
        $productData['name']  = $product->getName();
        $productData['price'] = $this->getPrice($product);
        if ($this->getProductBrand($product)) {
            $productData['brand'] = $this->getProductBrand($product);
        }

        if ($this->getColor($product)) {
            $productData['variant'] = $this->getColor($product);
        }
        if (!empty($this->getCategoryNameByProduct($product))) {
            $productData['category'] = $this->getCategoryNameByProduct($product);
        }
        $productData['quantity'] = $quantity;

        $data = [
            'event'     => 'removeFromCart',
            'ecommerce' => [
                'currencyCode' => $this->getCurrentCurrency(),
                'remove'       => [
                    'products' => [$productData]
                ]
            ]
        ];

        return $data;
    }

    /**
     * Get data layered in product detail page
     *
     * @param Product $product
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductDetailData($product)
    {
        $categoryPath = '';
        $path         = $this->getBreadCrumbsPath();
        $useIdOrSku   = $this->getGTMUseIdOrSku($this->getStoreId());
        if (count($path) > 1) {
            array_pop($path);
            $categoryPath = implode(' > ', $path);
        }

        $productData        = [];
        $productData['id']  = $useIdOrSku ? $product->getSku() : $product->getId();
        $productData['sku'] = $product->getSku();
        if ($this->getColor($product)) {
            $productData['variant'][] = $this->getColor($product);
        }
        $productData['name'] = $product->getName();

        $productData['price'] = $this->getPrice($product);
        if ($this->getProductBrand($product)) {
            $productData['brand'] = $this->getProductBrand($product);
        }
        $productData['attribute_set_id']   = $product->getAttributeSetId();
        $productData['attribute_set_name'] = $this->_attributeSet
            ->get($product->getAttributeSetId())->getAttributeSetName();

        if ($product->getCategory()) {
            $productData['category'] = $product->getCategory()->getName();
        }

        if ($categoryPath) {
            $productData['category_path'] = $categoryPath;
        }

        $data = [
            'ecommerce' => [
                'detail' => [
                    'actionField' => [
                        'list' => $product->getCategory() ? $product->getCategory()->getName() : 'Product View'
                    ],
                    'products'    => [$productData]
                ]
            ]
        ];

        return $data;
    }

    /**
     * @param Product $product
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getViewProductData($product)
    {
        $useIdOrSku = $this->getAnalyticsUseIdOrSku($this->getStoreId());

        return [
            'id'        => $useIdOrSku ? $product->getSku() : $product->getId(),
            'name'      => $product->getName(),
            'list_name' => $product->getCategory() ? $product->getCategory()->getName() : 'Product View',
            'brand'     => $this->getProductBrand($product),
            'category'  => $this->getCategoryNameByProduct($product),
            'variant'   => $this->getColor($product),
            'quantity'  => 1,
            'price'     => $this->getPrice($product)
        ];
    }

    /**
     * @param $item
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCheckoutProductData($item)
    {
        $product    = $this->getProduct($item);
        $useIdOrSku = $this->getAnalyticsUseIdOrSku($this->getStoreId());

        return [
            'id'        => $useIdOrSku ? $product->getSku() : $product->getId(),
            'name'      => $product->getName(),
            'list_name' => 'Cart View',
            'brand'     => $this->getProductBrand($product),
            'category'  => $this->getCategoryNameByProduct($product),
            'variant'   => $this->getColor($product),
            'quantity'  => $item->getQtyOrdered() ?: 1,
            'price'     => $this->getPrice($product)
        ];
    }

    /**
     * @param Product $product
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFBProductView($product)
    {
        $useIdOrSku = $this->getPixelUseIdOrSku($this->getStoreId());
        $price      = $this->convertPrice($this->_catalogPrice->getCatalogPrice($product));

        return [
            'content_ids'  => [$useIdOrSku ? $product->getSku() : $product->getId()],
            'content_name' => $product->getName(),
            'content_type' => 'product',
            'contents'     => [
                [
                    'id'       => $useIdOrSku ? $product->getSku() : $product->getId(),
                    'name'     => $product->getName(),
                    'price'    => $price,
                    'quantity' => '1'
                ]
            ],
            'currency'     => $this->getCurrentCurrency(),
            'value'        => $price
        ];
    }

    /**
     * @param Item $item
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFBProductCheckOutData($item)
    {
        $product    = $this->getProduct($item);
        $useIdOrSku = $this->getPixelUseIdOrSku($this->getStoreId());

        return [
            'id'       => $useIdOrSku ? $product->getSku() : $product->getId(),
            'name'     => $product->getName(),
            'price'    => $this->getPrice($product),
            'quantity' => $item->getQty() ?: 1
        ];
    }

    /**
     * @param $item
     *
     * @return Product
     */
    public function getProduct($item)
    {
        if ($item->getProductType() === 'configurable') {
            $selectedProduct = $this->_productFactory->create();
            $selectedProduct->load($selectedProduct->getIdBySku($item->getSku()));
        } else {
            $selectedProduct = $item->getProduct();
        }

        return $selectedProduct;
    }

    /**
     * @param $item
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductCheckOutData($item)
    {
        $selectedProduct = $this->getProduct($item);
        $useIdOrSku      = $this->getGTMUseIdOrSku($this->getStoreId());

        $data = [
            'id'                 => $useIdOrSku ? $selectedProduct->getSku() : $selectedProduct->getId(),
            'name'               => $selectedProduct->getName(),
            'sku'                => $selectedProduct->getSku(),
            'price'              => $this->getPrice($selectedProduct),
            'attribute_set_id'   => $selectedProduct->getAttributeSetId(),
            'attribute_set_name' => $this->_attributeSet
                ->get($selectedProduct->getAttributeSetId())->getAttributeSetName()
        ];

        if ($this->getColor($selectedProduct)) {
            $data['variant'] = $this->getColor($selectedProduct);
        }

        if ($this->getProductBrand($selectedProduct)) {
            $data['brand'] = $this->getProductBrand($selectedProduct);
        }

        if (!empty($this->getCategoryNameByProduct($selectedProduct))) {
            $data['category'] = $this->getCategoryNameByProduct($selectedProduct);
        }

        return $data;
    }

    /**
     * @param $item
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductOrderedData($item)
    {
        $selectedProduct = $this->getProduct($item);
        $useIdOrSku      = $this->getGTMUseIdOrSku($this->getStoreId());

        $data['id']    = $useIdOrSku ? $selectedProduct->getSku() : $selectedProduct->getId();
        $data['name']  = $selectedProduct->getName();
        $data['price'] = $this->convertPrice($item->getBasePrice());

        if ($this->getColor($selectedProduct)) {
            $data['variant'] = $this->getColor($selectedProduct);
        }

        if ($this->getProductBrand($selectedProduct)) {
            $data['brand'] = $this->getProductBrand($selectedProduct);
        }

        if (!empty($this->getCategoryNameByProduct($selectedProduct))) {
            $data['category'] = $this->getCategoryNameByProduct($selectedProduct);
        }

        $data['attribute_set_id']   = $selectedProduct->getAttributeSetId();
        $data['attribute_set_name'] = $this->_attributeSet->get(
            $selectedProduct->getAttributeSetId()
        )->getAttributeSetName();
        $data['quantity']           = number_format($item->getQtyOrdered(), 0);

        return $data;
    }

    /**
     * @param $item
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFBProductOrderedData($item)
    {
        $selectedProduct = $this->getProduct($item);
        $useIdOrSku      = $this->getPixelUseIdOrSku($this->getStoreId());

        $productData             = [];
        $productData['id']       = $useIdOrSku ? $selectedProduct->getSku() : $selectedProduct->getId();
        $productData['name']     = $selectedProduct->getName();
        $productData['price']    = $this->getPrice($selectedProduct);
        $productData['quantity'] = number_format($item->getQtyOrdered(), 0);

        return $productData;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAffiliationName()
    {
        $webName   = $this->storeManager->getWebsite()->getName();
        $groupName = $this->storeManager->getGroup()->getName();
        $storeName = $this->storeManager->getStore()->getName();

        return $webName . '-' . $groupName . '-' . $storeName;
    }

    /**
     * Check the following modules is installed
     *
     * @param $moduleName
     *
     * @return bool
     */
    public function moduleIsEnable($moduleName)
    {
        $result = false;
        if ($this->_moduleManager->isEnabled($moduleName)) {
            switch ($moduleName) {
                case 'Mageplaza_Shopbybrand':
                    $result = true;
                    break;
                case 'Mageplaza_Osc':
                    $oscHelper = $this->objectManager->create('\Mageplaza\Osc\Helper\Data');
                    $result    = $oscHelper->isEnabled() ? true : false;
                    break;
            }
        }

        return $result;
    }

    /**
     * Get product brand if module Mageplaza_Shopbybrand is installed
     *
     * @param Product $product
     *
     * @return null
     */
    public function getProductBrand($product)
    {
        $attCode = null;
        if ($this->moduleIsEnable('Mageplaza_Shopbybrand')) {
            $sbbHelper    = $this->objectManager->create('\Mageplaza\Shopbybrand\Helper\Data');
            $brandFactory = $this->objectManager->create('\Mageplaza\Shopbybrand\Model\BrandFactory');
            if ($sbbHelper->getConfigGeneral('enabled') && $sbbHelper->getAttributeCode()) {
                $attCode = $sbbHelper->getAttributeCode();
                if ($this->_request->getFullActionName() === 'checkout_index_index') {
                    $product = $this->objectManager->create(Product::class)
                        ->load($product->getId());
                }
                $brand = $brandFactory->create()->loadByOption($product->getData($attCode))->getValue();

                return $brand;
            }

            return null;
        }

        return 'Default';
    }

    /**
     * Get color of configurable and simple product
     *
     * @param Product $product
     *
     * @return array|null|string
     */
    public function getColor($product)
    {
        $color = [];

        switch ($product->getTypeId()) {
            case 'configurable':
                $configurationAtt = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                foreach ($configurationAtt as $att) {
                    if ($att['label'] === 'Color') {
                        foreach ($att['values'] as $value) {
                            $color[] = $value['label'];
                        }
                        break;
                    }
                }
                $color = implode(',', $color);

                return $color;
            case 'simple':
                $table          = $this->objectManager->create(Table::class);
                $eavAttribute   = $this->objectManager->get(Attribute::class);
                $colorAttribute = $eavAttribute->load($eavAttribute->getIdByCode('catalog_product', 'color'));
                $allColor       = $table->setAttribute($colorAttribute)->getAllOptions(false);
                foreach ($allColor as $color) {
                    if ($color['value'] === $product->getData('color')) {
                        return $color['label'];
                    }
                }

                return null;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getBreadCrumbsPath()
    {
        $path        = [];
        $breadCrumbs = $this->_catalogHelper->getBreadcrumbPath();
        foreach ($breadCrumbs as $breadCrumb) {
            $path [] = $breadCrumb['label'];
        }

        return $path;
    }
}
