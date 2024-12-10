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

namespace Mageplaza\GoogleTagManager\Block\Tag;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\GoogleTagManager\Block\TagManager;

/**
 * Class PixelTag
 * @package Mageplaza\GoogleTagManager\Block\Tag
 */
class PixelTag extends TagManager
{

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPixelId($storeId = null)
    {
        return $this->_helper->getConfigPixel('tag_id', $storeId);
    }

    /**
     * Can show pixel
     * @return bool
     */
    public function canShowFbPixel()
    {
        return $this->_helper->isEnabled() && $this->_helper->getConfigPixel('enabled');
    }

    /**
     * @return array|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFbPageInfo()
    {
        $action = $this->getRequest()->getFullActionName();
        switch ($action) {
            case 'cms_index_index':
                return $this->getHomeData();
            case 'catalogsearch_result_index':
                return $this->getSearchData();
            case 'catalog_category_view':
                return $this->getCategoryData();
            case 'catalog_product_view':
                return $this->getProductView();
            case 'checkout_index_index':
            case 'checkout_cart_index':
                return $this->getFBCheckoutProductData();
            case 'onestepcheckout_index_index':
                return $this->_helper->moduleIsEnable('Mageplaza_Osc') ? $this->getFBCheckoutProductData() : null;
            case 'checkout_onepage_success':
            case 'multishipping_checkout_success':
                return $this->getCheckoutSuccessData();
        }

        return null;
    }

    /**
     * @throws NoSuchEntityException
     */
    protected function getCheckoutSuccessData()
    {
        $order      = $this->_helper->getSessionManager()->getLastRealOrder();
		$order_id = $this->getRequest()->getParam('order_id');
		if($order_id){
			$objectManager     = \Magento\Framework\App\ObjectManager::getInstance();
			$order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
		}
        $products   = [];
        $productIds = [];
        $items      = $order->getItemsCollection([], true);
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
            $products[]   = $this->_helper->getFBProductOrderedData($item);
        }

        $data = [
            'track_type' => 'Purchase',
            'data'       => [
                'content_ids'  => $productIds,
                'content_name' => 'Purchase',
                'content_type' => 'product',
                'contents'     => $products,
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $order->getGrandTotal()
            ]
        ];

        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getProductView()
    {
        $currentProduct = $this->_helper->getGtmRegistry()->registry('product');
        $fbData         = $this->_helper->getFBProductView($currentProduct);
        $data           = [
            'track_type' => 'ViewContent',
            'data'       => $fbData
        ];

        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getHomeData()
    {
        $data ['ecommerce'] = [
            'currencyCode' => $this->_helper->getCurrentCurrency()
        ];

        return $data;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getSearchData()
    {
        $productSearch = $this->_getProductCollection();
        $productSearch->setCurPage($this->getPageNumber())->setPageSize($this->getPageLimit());
        $products   = [];
        $productIds = [];
        $values     = 0;

        foreach ($productSearch as $value) {
            $productIds[]    = $value->getId();
            $sub             = [];
            $sub['id']       = $value->getId();
            $sub['quantity'] = 1;
            $sub['name']     = $value->getName();
            $sub['price']    = $this->_helper->getPrice($value);
            $products[]      = $sub;
            $values          += $this->_helper->getPrice($value);
        }

        $data = [
            'track_type' => 'Search',
            'data'       => [
                'content_ids'  => $productIds,
                'content_name' => 'Search',
                'content_type' => 'product',
                'contents'     => $products,
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $values
            ]
        ];

        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getCategoryData()
    {
        $categoryId    = $this->_registry->registry('current_category')->getId();
        $category      = $this->_category->load($categoryId);
        $sort          = $this->_toolbar->getCurrentOrder();
        $dir           = $this->_toolbar->getCurrentDirection();
        $loadedProduct = $category->getProductCollection()->addAttributeToSelect('*')
            ->setOrder($sort, $dir);
        $loadedProduct->setCurPage($this->getPageNumber())->setPageSize($this->getPageLimit());

        $productIds = [];
        $products   = [];
        $value      = 0;
        foreach ($loadedProduct as $item) {
            $productIds[]        = $item->getId();
            $value               += $this->_helper->getPrice($item);
            $product             = [];
            $product['id']       = $item->getId();
            $product['name']     = $item->getName();
            $product['quantity'] = '1';
            $product['price']    = $this->_helper->getPrice($item);
            $products[]          = $product;
        }

        $data = [
            'track_type' => 'ViewContent',
            'data'       => [
                'content_ids'  => $productIds,
                'content_name' => $category->getName(),
                'content_type' => 'product',
                'contents'     => $products,
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $value
            ]
        ];

        return $data;
    }

    /**
     * @return null|string
     */
    public function getFBAddToCartData()
    {
        if ($this->_helper->getSessionManager()->getFBAddToCartData()) {
            return json_encode($this->_helper->getSessionManager()->getFBAddToCartData());
        }

        return null;
    }

    /**
     * Get Checkout Data
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function getFBCheckoutProductData()
    {
        $items      = $this->_cart->getQuote()->getAllVisibleItems();
        $products   = [];
        $productIds = [];
        $value      = 0;

        if (empty($items)) {
            return [];
        }

        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
            $products[]   = $this->_helper->getFBProductCheckOutData($item);
            $value        += $this->_helper->getFBProductCheckOutData($item)['price'];
        }

        $data = [
            'track_type' => 'InitiateCheckout',
            'data'       => [
                'content_ids'  => $productIds,
                'content_name' => 'checkout',
                'content_type' => 'product',
                'contents'     => $products,
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $value
            ]
        ];

        return $data;
    }
}
