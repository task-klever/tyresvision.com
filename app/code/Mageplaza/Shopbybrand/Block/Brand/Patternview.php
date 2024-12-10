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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Brand;

use Mageplaza\Shopbybrand\Block\Brand;


/**
 * Class View
 * @package Mageplaza\Shopbybrand\Block
 */
class Patternview extends Brand
{
    /**
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		$pattern = $this->getPattern();
		$title = $pattern->getMetaTitle() ?: $pattern->getValue();
		if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
			$breadcrumbsBlock->addCrumb('view', ['label' => $title]);
		}

		$description = $pattern->getMetaDescription();
		if ($description) {
			$this->pageConfig->setDescription($description);
		}
		$keywords = $pattern->getMetaKeywords();
		if ($keywords) {
			$this->pageConfig->setKeywords($keywords);
		}

		$pageMainTitle = $this->getLayout()->getBlock('page.main.title');
		if ($pageMainTitle) {
			$pageMainTitle->setPageTitle($title);
		}

		return $this;
	}

    //************************* Init BrandView Breadcrumbs ***************************
    /**
     * @param $block
     *
     * @return $this
     */
	protected function additionCrumb($block)
	{
		$title = $this->getPageTitle();
		$block->addCrumb(
			'pattern',
			[
				'label' => __($title),
				'title' => __($title),
				'link'  => $this->helper->getBrandUrl()
			]
		);

		$pattern      = $this->getPattern();
		$patternTitle = $pattern->getPageTitle() ?: $pattern->getValue();
		$block->addCrumb('view', ['label' => $patternTitle]);

		return $this;
	}

    /**
     * @return $this
     */
	protected function initBreadcrumbs()
	{
		if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
			$title = $this->getPageTitle();

			$breadcrumbsBlock->addCrumb(
				'home',
				[
					'label' => __('Home'),
					'title' => __('Go to Home Page'),
					'link'  => $this->_storeManager->getStore()->getBaseUrl()
				]
			);
			$breadcrumbsBlock->addCrumb(
				'pattern',
				[
					'label' => __($title),
					'title' => __($title),
					'link'  => $this->helper->getBrandUrl()
				]
			);
		}

		return $this;
	}

    //************************* Get Brand CMS Block information to show on Catalog_View frontend ***************************
	/**
	 * @return mixed
	 */
	 
	public function getPattern()
	{
		return $this->_coreRegistry->registry('current_pattern');
	}
	
	public function getBrandImage()
	{
		$brand = $this->getBrand();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$pattern       = $this->getPattern();
		$brandId 	   = $pattern->getBrandId();
		$store = null;
		$brand = $objectManager->get('Mageplaza\Shopbybrand\Model\Brand')->loadByOption($brandId);
		return $this->helper()->getBrandImageUrl($brand);
	}
	
	public function getPatternProducts()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$pattern      = $this->getPattern();
		$brandId = $pattern->getBrandId();
		$patternId = $pattern->getPatternId();
		$productFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$collection = $productFactory->create();
		$collection->addAttributeToSelect('entity_id');
		$collection->addAttributeToSelect('diameter');
		$collection->addAttributeToSelect('diameter_value');
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		$collection->addAttributeToFilter('brand', $brandId);
		$collection->addAttributeToFilter('pattern', $patternId);
		//$collection->getSelect()->group('diameter');
		//echo '<pre>';print_r($collection->getData());die;
		return $collection;
	}
	
	public function group_by($key, $data) {
		$result = array();

		foreach($data as $val) {
			if(array_key_exists($key, $val)){
				$result[$val[$key]][] = $val;
			}else{
				$result[""][] = $val;
			}
		}

		return $result;
	}
	

}
