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
 * Class TagManager
 * @package Mageplaza\GoogleTagManager\Block\Tag
 */
class ManagerTag extends TagManager
{
    /**
     * Get GTM Id
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTagId($storeId = null)
    {
        return $this->_helper->getConfigGTM('tag_id', $storeId);
    }

    /**
     * Check condition show page
     * @return bool
     */
    public function canShowGtm()
    {
        return $this->_helper->isEnabled() && $this->_helper->getConfigGTM('enabled');
    }

    /**
     * Tag manager dataLayer
     * @return array|null
     * @throws LocalizedException
     */
    public function getGtmDataLayer()
    {
        $action = $this->getRequest()->getFullActionName();
        switch ($action) {
            case 'cms_index_index':
                return $this->encodeJs($this->getHomeData());
            case 'catalogsearch_result_index':
                return $this->encodeJs($this->getSearchData());
            case 'catalog_category_view': // Product list page
                return $this->encodeJs($this->getCategoryData());
            case 'catalog_product_view': // Product detail view page
                return $this->encodeJs($this->getProductView());
            case 'checkout_index_index':  // Checkout page
                return $this->encodeJs($this->getCheckoutProductData('2'));
            case 'checkout_cart_index':   // Shopping cart
                return $this->encodeJs($this->getCheckoutProductData('1'));
            case 'onestepcheckout_index_index': // Mageplaza One step check out page
                return $this->encodeJs($this->getCheckoutProductData('1'));
            case 'checkout_onepage_success': // Purchase page
            case 'multishipping_checkout_success':
                return $this->encodeJs($this->getCheckoutSuccessData());
        }

        return $this->encodeJs($this->getDefaultData());
    }

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getCheckoutSuccessData()
    {
        $order    = $this->_helper->getSessionManager()->getLastRealOrder();

        $order_id = $this->getRequest()->getParam('order_id');

        if($order_id){
			$objectManager     = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
        }
                
        $products = [];
        $items    = $order->getItemsCollection([], true);
		
		$sub_total = $order->getSubtotal();
		$discount = $order->getDiscountAmount();
		$shipping = $order->getBaseShippingAmount();
		$tax = $order->getBaseTaxAmount();
		$revenue = $order->getGrandTotal();
		$total = $order->getGrandTotal();
		
        foreach ($items as $item) {
            $products[] = $this->_helper->getProductOrderedData($item);
        }
        $data ['ecommerce'] = [
            'purchase' => [
                'actionField' => [
                    'id'          => $order->getIncrementId(),
                    'affiliation' => $this->_helper->getAffiliationName(),
                    'order_id'    => $order->getIncrementId(),
                    'subtotal'    => $sub_total,
                    'shipping'    => $shipping,
                    'tax'         => $tax,
                    'total'       => $total,
                    'revenue'     => $revenue,
                    'discount'    => $discount,
                    'coupon'      => (string) $order->getCouponCode()
                ],
                'products'    => $products
            ]
        ];
        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getDefaultData()
    {
        $data = [
            'dynx_itemid'     => '0',
            'dynx_pagetype'   => 'other',
            'dynx_totalvalue' => '0',
            'ecommerce'       => [
                'currencyCode' => $this->_helper->getCurrentCurrency()
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

        return $this->_helper->getProductDetailData($currentProduct);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getHomeData()
    {
        $data = [
            'dynx_itemid'     => '0',
            'dynx_pagetype'   => 'home',
            'dynx_totalvalue' => '0',
            'ecommerce'       => [
                'currencyCode' => $this->_helper->getCurrentCurrency()
            ]
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
        $data = [
            'dynx_itemid'     => '0',
            'dynx_pagetype'   => 'searchresults',
            'dynx_totalvalue' => '0',
            'ecommerce'       => [
                'currencyCode' => $this->_helper->getCurrentCurrency()
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
        /** get current breadcrumb path name */
        $path     = $this->_helper->getBreadCrumbsPath();
        $products = [];
        $result   = [];
        $i        = 0;

        $categoryId    = $this->_registry->registry('current_category')->getId();
        $category      = $this->_category->load($categoryId);
        $sort          = $this->_toolbar->getCurrentOrder();
        $dir           = $this->_toolbar->getCurrentDirection();
        $loadedProduct = $category->getProductCollection()->addAttributeToSelect('*')
            ->setOrder($sort, $dir);
        $loadedProduct->setCurPage($this->getPageNumber())->setPageSize($this->getPageLimit());

        foreach ($loadedProduct as $item) {
            $i++;
            $products[$i]['id']   = $item->getId();
            $products[$i]['name'] = $item->getName();

            $products[$i]['price']    = $this->_helper->getPrice($item);
            $products[$i]['list']     = $category->getName();
            $products[$i]['position'] = $i;
            $products[$i]['category'] = $category->getName();
            if ($this->_helper->getProductBrand($item)) {
                $products[$i]['brand'] = $this->_helper->getProductBrand($item);
            }
            if ($this->_helper->getColor($item)) {
                $products[$i]['variant'] = $this->_helper->getColor($item);
            }
            $products[$i]['path']          = implode(' > ', $path) . ' > ' . $item->getName();
            $products[$i]['category_path'] = implode(' > ', $path);
            $result []                     = $products[$i];
        }

        $data ['ecommerce'] = [
            'currencyCode' => $this->_helper->getCurrentCurrency(),
            'impressions'  => $result
        ];

        return $data;
    }

    /**
     * Get product data in checkout page
     *
     * @param $step
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCheckoutProductData($step)
    {
        $cart = $this->_cart;
        // retrieve quote items array
        $items    = $cart->getQuote()->getAllVisibleItems();
        $products = [];

        if (empty($items)) {
            return [];
        }

        foreach ($items as $item) {
            $products[] = $this->_helper->getProductCheckOutData($item);
        }

        $data = [
            'event'     => 'checkout',
            'ecommerce' => [
                'checkout' => [
                    'actionField' => [
                        'step' => $step
                    ],
                    'products'    => $products,
                    'hasItems'    => $cart->getQuote()->hasData(),
                    'hasCoupon'   => $cart->getQuote()->getCouponCode() ? true : false,
                    'coupon'      => $cart->getQuote()->getCouponCode() ?: '',
                    'total'       => $cart->getQuote()->getData('grand_total'),
                ]
            ]
        ];

        return $data;
    }
}
