<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Model\XmlFeed;

use Magefan\GoogleShoppingFeed\Model\Config;
use Magefan\GoogleShoppingFeed\Setup\Patch\Data\AddMfGoogleProductAttribute;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Framework\UrlInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Helper\Output as CatalogOutputHelper;
use Hdweb\Tyrefinder\Helper\Productlisting as ProductlistingHelper;

class ProductData
{
    const ANALYTICS_SETUP_FIELDS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Processor
     */
    private $imageProcessor;

    /**
     * @var ConfigurableProduct
     */
    private $configurableProduct;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var array
     */
    private $categoryProduct;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CatalogOutputHelper
     */
    private $catalogOutputHelper;

    /**
     * @var ProductlistingHelper
     */
    private $productlistingHelper;


    /**
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Config $config
     * @param Processor $imageProcessor
     * @param ConfigurableProduct $configurableProduct
     * @param StockRegistryInterface $stockRegistry
     * @param Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param CatalogOutputHelper $catalogOutputHelper
     * @param ProductlistingHelper $productlistingHelper
     */
    public function __construct(
        StoreManagerInterface       $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Config                      $config,
        Processor                   $imageProcessor,
        ConfigurableProduct         $configurableProduct,
        StockRegistryInterface      $stockRegistry,
        Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        CatalogOutputHelper $catalogOutputHelper,
        ProductlistingHelper $productlistingHelper
    )
    {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->config = $config;
        $this->imageProcessor = $imageProcessor;
        $this->configurableProduct = $configurableProduct;
        $this->stockRegistry = $stockRegistry;
        $this->registry = $registry;
        $this->priceCurrency = $priceCurrency;
        $this->catalogOutputHelper = $catalogOutputHelper;
        $this->productlistingHelper = $productlistingHelper;
    }

    /**
     * @param $product
     * @param $attribute
     * @return string
     * @throws NoSuchEntityException
     */
    public function getData($product, $attribute): string
    {
        $method = $this->getFuncName((string)$attribute);
        
        if (method_exists($this, $method)) {
            $value = $this->$method($product);
        } elseif (method_exists($product, $method)) {
            if($attribute == 'name'){
                $brandTitle = '';
                $tyreSizeAndLoadIndex = '';
                $pattern = '';
                $year = '';
                $brand = $this->catalogOutputHelper->productAttribute($product, $product->getMgsBrand(), 'mgs_brand');
                if($brand){
                    $brandDetails = $this->productlistingHelper->getBrandDetails($brand);
                    $brandTitle = $brandDetails->getPageTitle();
                }

                $tyreSizeAndLoadIndex = $this->productlistingHelper->getTyreSize($product, true);
                $pattern = $product->getResource()->getAttribute("pattern")->getFrontend()->getValue($product);
                $year = $product->getResource()->getAttribute("year")->getFrontend()->getValue($product);
                $value = $brandTitle;
                $value .= ($tyreSizeAndLoadIndex != '') ? ' - Tyre Size: ' . $tyreSizeAndLoadIndex : '';
                $value .= ($pattern != '') ? ' ' . $pattern : '';
                $value .= ($year != '') ? ' - Year of ' . $year : '';
            }else{
                $value = $product->{$method}();
            }
            
            
        } else {
            if($attribute == 'description'){
                $productName = '';
                $productName = $product->getName();
                $value = 'Buy '.$productName.' tyres online in TyresVision. Find and purchase tires online easily through Tyres Online, your top choice for buying tires on the web in TyresVision. Discover the best tire selections, exclusive offers, and competitive prices on a wide range of car tyres available for online purchase in TyresVision.';
            }else{
                $value = $this->getAttribute($product, $attribute);
            }
            
        }

        if ($value && in_array($attribute, $this->imageProcessor->getMediaAttributeCodes())
            && false === strpos($value, '://')
        ) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $mediaCatalogProductUrl = $baseUrl.'media/catalog/product';
            //$value = $this->getMediaCatalogProductUrl() . $value;
            $value = $mediaCatalogProductUrl.$value;
        }

        return $value ?: '';
    }

    /**
     * @param string $value
     * @return string
     */
    private function getFuncName(string $value): string
    {
        return "get" . str_replace(' ', '', ucwords(strtolower(str_replace("_", " ", $value))));
    }

    /**
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductType($product): string
    {
        $values = [];
        if ($productCategory = $this->getCategoryByProduct($product)) {
            $categoryIds = $productCategory->getPathIds();
            foreach ($categoryIds as $categoryId) {
                $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
                if ($category->getLevel() < 2) {
                    continue;
                }
                $values[] = $category->getName();
            }
        }

        $value = implode(" > ", $values);
        return strip_tags(html_entity_decode($value));
    }

    /**
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getGoogleProductCategory($product): string
    {
        if ($googleIdProduct = $this->getGoogleIdProduct($product)) {
            return $googleIdProduct;
        }

        $parentProduct = $product->getMfParentProduct();
        if ($parentProduct && ($googleIdProduct = $this->getGoogleIdProduct($parentProduct))) {
            return $googleIdProduct;
        }

        if ($productCategory = $this->getCategoryByProduct($product)) {
            return $this->getGoogleIdCategory($productCategory) ?: '';
        }

        return '';
    }

    /**
     * @param $product
     * @return false|\Magento\Catalog\Api\Data\CategoryInterface|mixed
     * @throws NoSuchEntityException
     */
    private function getCategoryByProduct($product)
    {
        if (!isset($this->categoryProduct[$product->getId()])) {
            $productCategory = false;

            $categoryIds = $product->getCategoryIds();

            $parentProduct = $product->getMfParentProduct();
            if ($parentProduct) {
                $categoryIds = array_unique(array_merge($categoryIds, $parentProduct->getCategoryIds()));
            }

            if ($categoryIds) {
                $level = -1;
                $store = $this->storeManager->getStore();
                $rootCategoryId = $store->getRootCategoryId();

                foreach ($categoryIds as $categoryId) {
                    try {
                        $category = $this->categoryRepository->get($categoryId, $store->getId());
                        if ($category->getIsActive()
                            && $category->getLevel() > $level
                            && in_array($rootCategoryId, $category->getPathIds())
                        ) {
                            $level = $category->getLevel();
                            $productCategory = $category;
                        }
                    } catch (\Exception $e) {
                        /* Do nothing */
                    }
                }

            }

            $this->categoryProduct[$product->getId()] = $productCategory;
        }

        return $this->categoryProduct[$product->getId()];
    }

    /**
     * Google Category Id
     * from Product Settings
     *
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    private function getGoogleIdProduct($product): string
    {
        $productAttribute = $product->getData(AddMfGoogleProductAttribute::MF_GOOGLE_PRODUCT_PRODUCT);
        if (null === $productAttribute) {
            $productAttribute = $product->getResource()->getAttributeRawValue(
                $product->getId(),
                AddMfGoogleProductAttribute::MF_GOOGLE_PRODUCT_PRODUCT,
                $this->storeManager->getStore()->getId()
            );
        }
        if (empty($productAttribute) || $productAttribute == '0') {
            return '';
        }

        return $productAttribute;
    }

    /**
     * Google Category Id
     * from Category Settings
     *
     * @param array $categoryIds
     * @param $store
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    private function getGoogleIdCategory($category)
    {
        $categoryIds = $category->getPathIds();
        if (empty($categoryIds) || !is_array($categoryIds)) {
            return false;
        }

        foreach (array_reverse($categoryIds, true) as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
                $googleAttribute = $category->getCustomAttribute(
                    AddMfGoogleProductAttribute::MF_GOOGLE_PRODUCT_CATEGORY
                );

                if ($googleAttribute && $googleAttribute->getValue() !== '0') {
                    return $googleAttribute->getValue();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return false;
    }

    /**
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPrice($product): string
    {
        $priceFloat = (float)$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();

        if (!$priceFloat) {
            return '';
        }

        $currentCurrency = $this->getCurrentCurrencySymbol();
        $price = $this->priceCurrency->convert($priceFloat, $this->storeManager->getStore(), $currentCurrency);

        return number_format($price, 2, '.', '') . ' ' . $currentCurrency;
    }

    /**
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSpecialPrice($product): string
    {
        $priceFloat = (float)$product->getPriceInfo()->getPrice('special_price')->getAmount()->getValue();

        if (!$priceFloat) {
            return '';
        }

        $currentCurrency = $this->getCurrentCurrencySymbol();
        $price = $this->priceCurrency->convert($priceFloat, $this->storeManager->getStore(), $currentCurrency);

        return number_format($price, 2, '.', '') . ' ' . $currentCurrency;
    }

    /**
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getFinalPrice($product): string
    {
        $finalPriceFloat = (float)$product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $finalPriceCmp = number_format($finalPriceFloat, 1, '.', '');

        $priceFloat = (float)$product->getPrice();
        if (!$priceFloat) {
            $priceFloat = (float)$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        }

        $priceCmp = number_format($priceFloat, 1, '.', '');

        if (!$finalPriceFloat || $priceCmp == $finalPriceCmp) {
            return '';
        }

        $currentCurrency = $this->getCurrentCurrencySymbol();
        $price = $this->priceCurrency->convert($finalPriceFloat, $this->storeManager->getStore(), $currentCurrency);

        return number_format($price, 2, '.', '') . ' ' . $currentCurrency;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getCurrentCurrencySymbol(): string
    {
        return $this->registry->registry('mf_current_currency');
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductUrl($product): string
    {
        $options = [];

        $parentProduct = $product->getMfParentProduct();
        if ($parentProduct) {
            $productUrl = $parentProduct->getProductUrl();
            $options['mfpreselect'] = $parentProduct->getId();

            $attributes = $this->configurableProduct->getConfigurableAttributesAsArray($parentProduct);
            foreach ($attributes as $attribute) {
                $id = $attribute['attribute_id'];
                $value = $product->getData($attribute['attribute_code']);
                $options[$id] = $value;
            }
        } else {
            $productUrl = $product->getProductUrl();
        }

        foreach (self::ANALYTICS_SETUP_FIELDS as $field) {
            $param = $this->config->getGoogleAnalytics($field);
            if (!trim($param)) {
                continue;
            }
            $options[$field] = $param;
        }

        if ($options) {
            $productUrl .= (false === strpos($productUrl, '?')) ? '?' : '&';
            $productUrl .= http_build_query($options);
        }

        return $productUrl;
    }

    /**
     * @param $product
     * @param $attribute
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAttribute($product, $attribute): string
    {
        $result = '';

        if ($value = $product->getData($attribute)) {
            if (!is_array($value)) {
                $attributeText = $product->getAttributeText($attribute);
                $result = $attributeText ? $attributeText : $value;
                $result = (string)$result;
            }
        }

        $parentProduct = $product->getMfParentProduct();
        if (!$result && $parentProduct) {
            $result = $this->getAttribute($parentProduct, $attribute);
        }

        return $result;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getMediaCatalogProductUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true)
            . 'catalog/product';
    }

    /**
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getQuantityAndStockStatus($product): string
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        if (!$stockItem) {
            return 'out_of_stock';
        }
        return $stockItem->getIsInStock() ? 'in_stock' : 'out_of_stock';
    }

    /**
     * @param $product
     * @return string
     */
    public function getDynamicGtin($product): string
    {
        $id = $product->getId();
        $gtin = $id;
        $i = 0;
        $prefix = '';
        while (strlen($gtin) + strlen($prefix) < 11) {
            $i++;
            $prefix .= ($i < 10) ? $i : 0;
        }

        $gtin = $prefix . $gtin;
        $s1 = $s2 = 0;
        for ($i = 0; $i < strlen($gtin); $i++) {
            if ($i % 2) {
                $s2 .= $gtin[$i];
            } else {
                $s1 .= $gtin[$i];
            }
        }

        $s = $s1 * 3 + $s2;
        $l = 10 - ($s % 10);
        if ($l == 10) {
            $l = 0;
        }

        $gtin .= $l;

        return $gtin;
    }

}
