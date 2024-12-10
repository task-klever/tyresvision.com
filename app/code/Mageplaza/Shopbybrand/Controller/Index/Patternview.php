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

namespace Mageplaza\Shopbybrand\Controller\Index;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class View
 *
 * @package Mageplaza\Shopbybrand\Controller\Index
 */
class Patternview extends Action
{
    /**
     * @type \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @type \Mageplaza\Shopbybrand\Helper\Data
     */
    protected $helper;

    /**
     * @type \Mageplaza\Shopbybrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * @type \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @type \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @type \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory
     * @param \Mageplaza\Shopbybrand\Helper\Data                  $helper
     * @param \Mageplaza\Shopbybrand\Model\BrandFactory           $brandFactory
     * @param \Magento\Framework\Registry                         $coreRegistry
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface    $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface          $storeManager
     * @param \Magento\Framework\Json\Helper\Data                 $jsonHelper
     */
    public function __construct(Context $context,
        PageFactory $resultPageFactory, Helper $helper,
        BrandFactory $brandFactory, Registry $coreRegistry,
        ForwardFactory $resultForwardFactory,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandFactory = $brandFactory;
        $this->helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->helper->getGeneralConfig('enabled')){
            $pattern = $this->_initPattern();
            if ($pattern) {
                $this->getRequest()->setParam(
                    $this->helper->getAttributeCode(), $pattern->getId()
                );
				
                $page = $this->resultPageFactory->create();
                $page->getConfig()->addBodyClass('page-products');

                if ($this->getRequest()->isAjax()) {
                    $layout = $page->getLayout();
                    $result = [
                        'products'   => $layout->getBlock('brand.patten.view')->toHtml(),
                        'navigation' => $layout->getBlock('catalog.leftnav')->toHtml()
                    ];
                    return $this->getResponse()->representJson(
                        $this->_jsonHelper->jsonEncode($result)
                    );
                }
				
				$title = $pattern->getMetaTitle();
				if ($title) {
					$page->getConfig()->getTitle()->set($title);
				}
				$description = $pattern->getMetaDescription();
				if ($description) {
					$page->getConfig()->setDescription($description);
				}
				$keywords = $pattern->getMetaKeywords();
				if ($keywords) {
					$page->getConfig()->setKeywords($keywords);
				}
				
                return $page;
            }
        }
        else{
            return $this->resultForwardFactory->create()->forward('noroute');
        }

    }

    /**
     * @return bool
     */
    protected function _initPattern()
    {
		$urlKey = $this->getRequest()->getParam('pattern_key');
		
        if (!$urlKey) {
            return false;
        }
		
       // $currentBrand = false;
        $currentPattern = false;
        try {
            /* $brandCollection = $this->_brandFactory->create()
                ->getBrandCollection();
            foreach ($brandCollection as $brand) {
				//echo '<pre>';print_r($brand);die;
                if ($this->helper->processKey($brand) == $urlKey) {
                    $currentBrand = $brand;
                    break;
                }
            } */
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$brandPatternObj = $objectManager->get('Mageplaza\Shopbybrand\Model\PatternmanagementFactory');
			$brandPatternCollection = $brandPatternObj->create()->getCollection()
									->addFieldToFilter('url_key', $urlKey);
			//echo '<pre>';print_r($brandPatternCollection->getData());die;
            foreach ($brandPatternCollection as $pattern) {
                if($pattern->getUrlKey() == $urlKey) {
                    $currentPattern = $pattern;
                    break;
                }
            }
			
            $category = $this->categoryRepository->get(
                $this->_storeManager->getStore()->getRootCategoryId()
            );
        } catch (NoSuchEntityException $e) {
            return false;
        }
        $this->_coreRegistry->register('current_pattern', $currentPattern);
        $this->_coreRegistry->register('current_category', $category);

        return $currentPattern;
    }
}
