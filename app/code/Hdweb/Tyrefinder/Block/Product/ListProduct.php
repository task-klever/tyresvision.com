<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Hdweb\Tyrefinder\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;

/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected $productCollectionFactory;
    protected $_categoryCollection;
    protected $_storeManager;
    protected $request;
    protected $salesrule;
    protected $catalogrule;
    protected $serialize;
    protected $_defaultToolbarBlock = Toolbar::class;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\SalesRule\Model\Rule $salesrule,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\CatalogRule\Model\Rule $catalogrule,
        array $data = []
    ) {
        $this->_catalogLayer            = $layerResolver->get();
        $this->_postDataHelper          = $postDataHelper;
        $this->categoryRepository       = $categoryRepository;
        $this->urlHelper                = $urlHelper;
        $this->_scopeConfig             = $scopeConfig;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_categoryCollection      = $categoryCollection;
        $this->_storeManager            = $storeManager;
        $this->request                  = $request;
        $this->salesrule                = $salesrule;
        $this->serializer               = $serializer;
        $this->catalogrule              = $catalogrule;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper);
    }

    protected function _getProductCollection()
    {

        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }

        return $this->_productCollection;
    }

    public function getLayer()
    {
        return $this->_catalogLayer;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {

        return $this->_getProductCollection();
    }

    public function getRearcollection()
    {
        $width_rear                  = $this->getRequest()->getParam('width_rear');
        $height_rear                 = $this->getRequest()->getParam('height_rear');
        $rim_rear                    = $this->getRequest()->getParam('rim_rear');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productStatus = $objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
		$productVisibility = $objectManager->get('Magento\Catalog\Model\Product\Visibility');
        $rear_tyre_search_collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
			->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()])
			->setVisibility($productVisibility->getVisibleInSiteIds())
            ->addFieldToFilter('width', $width_rear)
            ->addFieldToFilter('height', $height_rear)
            ->addFieldToFilter('rim', $rim_rear);

        return $rear_tyre_search_collection;
    }
	
	public function getFrontcollection()
    {
        $width                  = $this->getRequest()->getParam('width');
        $height                 = $this->getRequest()->getParam('height');
        $rim                    = $this->getRequest()->getParam('rim');
        $front_tyre_search_collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('width', $width)
            ->addFieldToFilter('height', $height)
            ->addFieldToFilter('rim', $rim);

        return $front_tyre_search_collection;
    }

    public function getBundleCollection()
    {
        $allBundleItems = array();
        if ($this->isBundle()) {
            $rearcollection = $this->getRearcollection();
            //$frontcollection = $this->getFrontcollection();
			$frontcollection = $this->_productCollection;
            $isBundleFound  = 0;
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$listingHelper   = $objectManager->create('Hdweb\Tyrefinder\Helper\Productlisting');
            foreach ($frontcollection as $fronProduct) {
                $FrontbrandId   = $fronProduct->getMgsBrand();
                $FrontpatternId = $fronProduct->getPattern();
                $FrontyearId    = $fronProduct->getYear();
                $FrontrunflatId = $fronProduct->getRunflat();

                foreach ($rearcollection as $rearProduct) {
                    if (($FrontbrandId != $rearProduct->getMgsBrand()) || ($fronProduct->getId() == $rearProduct->getId()) || ($FrontpatternId != $rearProduct->getPattern()) || ($FrontyearId != $rearProduct->getYear()) || ($FrontrunflatId != $rearProduct->getRunflat()) || ($fronProduct->getIsSalable() != $rearProduct->getIsSalable())) {
                        continue;
                    }
					
					$frontset2price = $listingHelper->getSet2price($fronProduct);
					$front_price = str_replace("AED","",$frontset2price); 
					$front_price = str_replace(",","",$front_price);
					$rearset2price = $listingHelper->getSet2price($rearProduct);
					$rear_price = str_replace("AED","",$rearset2price); 
					$rear_price = str_replace(",","",$rear_price);
					$bundleset4price = $front_price + $rear_price;
					
                    $allBundleItems[] = array('front' => $fronProduct->getId(), 'rear' => $rearProduct->getId(), 'bundle_price' => $bundleset4price);
                }
            }
			/* start bundle price sort */ 
			array_multisort(array_map(function($element) {
				return $element['bundle_price'];
			}, $allBundleItems), SORT_ASC, $allBundleItems);
			/* end bundle price sort */ 
        }

        return $allBundleItems;
    }

    public function isBundle()
    {
        $width_rear  = $this->getRequest()->getParam('width_rear');
        $height_rear = $this->getRequest()->getParam('height_rear');
        $rim_rear    = $this->getRequest()->getParam('rim_rear');
        $isBundle    = 0;
        if (isset($width_rear) && isset($height_rear) && isset($rim_rear) && !empty($width_rear) && !empty($height_rear) && !empty($rim_rear)) {
            $isBundle = 1;
        }

        return $isBundle;
    }

    private function initializeProductCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId(current($categories->getIterator())->getId());
            }
        }

        $origCategory = null;

        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }
        $collection = $layer->getProductCollection();
        // $width_front  = $this->getRequest()->getParam('width');
        // $height_front = $this->getRequest()->getParam('height');
        // $rim_front    = $this->getRequest()->getParam('rim');
        // if (isset($width_front) && isset($height_front) && isset($rim_front)) {
        //     $rear_tyre_search_collection = $this->productCollectionFactory->create()
        //         ->addAttributeToSelect('*')
        //         ->addFieldToFilter('width', $width_front)
        //         ->addFieldToFilter('height', $height_front)
        //         ->addFieldToFilter('rim', $rim_front);
        //     $collection = $rear_tyre_search_collection;
        // }

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $this->addToolbarBlock($collection);

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    public function getToolbarBlock()
    {
        $block = $this->getToolbarFromLayout();

        if (!$block) {
            $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        }

        return $block;
    }

    public function addToolbarBlock(Collection $collection)
    {
        $toolbarLayout = $this->getToolbarFromLayout();

        if ($toolbarLayout) {
            $this->configureToolbar($toolbarLayout, $collection);
        }
    }
	
	public function getRecommendedProducts(){
		$productIds = $this->_productCollection->getAllIds();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productStatus = $objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
		$productVisibility = $objectManager->get('Magento\Catalog\Model\Product\Visibility');
		$listingHelper   = $objectManager->create('Hdweb\Tyrefinder\Helper\Productlisting');
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
			->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()])
			->setVisibility($productVisibility->getVisibleInSiteIds())
            ->addFieldToFilter('entity_id', array('in' => $productIds))
            ->addFieldToFilter('isrecomended', 1)
			->setOrder('price', 'asc')
			->setPageSize(4);
		$recomendedSortByPrice = array();	
		foreach ($collection as $recomendedProduct) {
			$set4price=$listingHelper->getSet4price($recomendedProduct);
			$set4price = str_replace("AED","",$set4price); 
			$set4priceFinal = str_replace(",","",$set4price);
			$recomendedSortByPrice[] = array('id' => $recomendedProduct->getId(), 'sortby_price' => $set4priceFinal);
		}
		/* start bundle price sort */ 
		array_multisort(array_map(function($element) {
			return $element['sortby_price'];
		}, $recomendedSortByPrice), SORT_ASC, $recomendedSortByPrice);
		/* end bundle price sort */ 		
		//echo '<pre>';print_r($recomendedSortByPrice);die;
		return $recomendedSortByPrice;	
	}

    public function getRecommendedProductsBKP()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $topProductIds = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('hdwebconfig/general/top_product_ids');
        if($topProductIds && $topProductIds != ''){
            $productIds = explode(',', $topProductIds);
            $productStatus = $objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
            $productVisibility = $objectManager->get('Magento\Catalog\Model\Product\Visibility');
            $listingHelper   = $objectManager->create('Hdweb\Tyrefinder\Helper\Productlisting');
            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()])
                ->setVisibility($productVisibility->getVisibleInSiteIds())
                ->addFieldToFilter('entity_id', array('in' => $productIds));
                //->setPageSize(4);
            $recomendedSortByPrice = array();	
            foreach ($collection as $recomendedProduct) {
                $set4price=$listingHelper->getSet4price($recomendedProduct);
                $set4price = str_replace("AED","",$set4price); 
                $set4priceFinal = str_replace(",","",$set4price);
                $recomendedSortByPrice[] = array('id' => $recomendedProduct->getId(), 'sortby_price' => $set4priceFinal);
            }
            /* start price sort */ 
            array_multisort(array_map(function($element) {
                return $element['sortby_price'];
            }, $recomendedSortByPrice), SORT_ASC, $recomendedSortByPrice);
            /* end price sort */
            return $recomendedSortByPrice;	
        }else{
            return [];
        }
	}

}
