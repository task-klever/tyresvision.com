<?php

namespace MGS\Ajaxlayernavigation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Search extends AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context, 
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) { 
        $this->_objectManager= $objectManager; 
        parent::__construct($context);
    }

    public function search()
    {
        $searchQuery = $this->_request->getParam('q');
        if (!$searchQuery) {
            return false;
        }

        $search = $this->_objectManager->create(
            '\Magento\Search\Api\SearchInterface');

        $searchCriteriaBuilder = $this->_objectManager->create(
            '\Magento\Framework\Api\Search\SearchCriteriaBuilder');

        $filterBuilder = $this->_objectManager->create(
            '\Magento\Framework\Api\FilterBuilder');

        $filterBuilder
            ->setField('search_term')
            ->setValue($searchQuery);

        $searchCriteriaBuilder->addFilter($filterBuilder->create());

        $searchCriteria = $searchCriteriaBuilder->create();

        $searchCriteria->setRequestName('quick_search_container');
        $items = $search->search($searchCriteria)->getItems();
        if (count($items) > 0) {
            $entityIds = [];
            foreach ($items as $item) {
                $entityIds[] = $item->getId();
            }

            return $entityIds;
        }
        return false;
    }
	
	public function layerFilterCollection($attributeCode)
    {
		$frontcollection = array();
		$allBundleItems = array();

		if ((count($this->getRearcollection()) > 0) && ($this->isBundle())) {
		$productCollectionFactory = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$productStatus = $this->_objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
		$productVisibility = $this->_objectManager->get('Magento\Catalog\Model\Product\Visibility');
		$request = $this->_objectManager->get('Magento\Framework\App\Request\Http');
		$width_rear                  = $request->getParam('width_rear');
        $height_rear                 = $request->getParam('height_rear');
        $rim_rear                    = $request->getParam('rim_rear');
		
		$width	= $request->getParam('width');
        $height	= $request->getParam('height');
        $rim	= $request->getParam('rim');
		/* $filterCollection = $productCollectionFactory->create()
									//->addAttributeToSelect('*')
									->addAttributeToSelect(array('entity_id','height','width','rim','mgs_brand','offers','pattern','year','manufacturer','runflat')) //add product attribute to be fetched
									->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()])
									->setVisibility($productVisibility->getVisibleInSiteIds())
									->addFieldToFilter('width', $width_rear)
									->addFieldToFilter('height', $height_rear)
									->addFieldToFilter('rim', $rim_rear); */
		$frontcollection = $productCollectionFactory->create()
                ->addAttributeToSelect(array('entity_id','height','width','rim','mgs_brand','offers','pattern','year','manufacturer','runflat')) //add product attribute to be fetched
				->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()])
				->setVisibility($productVisibility->getVisibleInSiteIds())
                ->addFieldToFilter('width', $width)
                ->addFieldToFilter('height', $height)
                ->addFieldToFilter('rim', $rim);
				
		$frontIds = array();		
		$rearIds = array();			
		foreach ($frontcollection as $fronProduct) {
			$FrontbrandId   = $fronProduct->getMgsBrand();
			$FrontpatternId = $fronProduct->getPattern();
			$FrontyearId    = $fronProduct->getYear();
			$FrontrunflatId = $fronProduct->getRunflat();

			$rearcollection = $this->getRearcollection();
			foreach ($rearcollection as $rearProduct) {
				if (($FrontbrandId != $rearProduct->getMgsBrand()) || ($fronProduct->getId() == $rearProduct->getId()) || ($FrontpatternId != $rearProduct->getPattern()) || ($FrontyearId != $rearProduct->getYear()) || ($FrontrunflatId != $rearProduct->getRunflat()) || ($fronProduct->getIsSalable() != $rearProduct->getIsSalable())) {
					continue;
				}
			   // $allBundleItems[] = array('front' => $fronProduct->getId(), 'rear' => $rearProduct->getId());
				$allBundleItems[] = $fronProduct;
				//$frontIds[] = $fronProduct->getId();
				//$rearIds[] = $rearProduct->getId();
				//echo 'rear--'.$rearProduct->getId().'---'.'--brand id--'.$rearProduct->getMgsBrand().'<br/>';
			}
		}
/*		$arrayCombine = array_merge($frontIds,$rearIds);
		$arrayUnique = array_unique($arrayCombine);
		if(count($arrayUnique) > 0){
			$frontcollection = $productCollectionFactory->create()
						->addAttributeToSelect('*')
						->addFieldToFilter('entity_id', array('in' => $arrayUnique));		
		}*/
		}
		//echo '<pre>';print_r($frontcollection->getData());die;
		return $allBundleItems;
		
	}
	
	public function getRearcollection()
    {
		$productCollectionFactory = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$productStatus = $this->_objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
		$productVisibility = $this->_objectManager->get('Magento\Catalog\Model\Product\Visibility');
		$request = $this->_objectManager->get('Magento\Framework\App\Request\Http');
        $width_rear                  = $request->getParam('width_rear');
        $height_rear                 = $request->getParam('height_rear');
        $rim_rear                    = $request->getParam('rim_rear');
        $rear_tyre_search_collection = $productCollectionFactory->create()
									->addAttributeToSelect('*')
									->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()])
									->setVisibility($productVisibility->getVisibleInSiteIds())
									->addFieldToFilter('width', $width_rear)
									->addFieldToFilter('height', $height_rear)
									->addFieldToFilter('rim', $rim_rear);

        return $rear_tyre_search_collection;
    }
	
	public function isBundle()
    {
		$request = $this->_objectManager->get('Magento\Framework\App\Request\Http');
		$width_rear                  = $request->getParam('width_rear');
        $height_rear                 = $request->getParam('height_rear');
        $rim_rear                    = $request->getParam('rim_rear');
        $isBundle    = 0;
        if (isset($width_rear) && isset($height_rear) && isset($rim_rear) && !empty($width_rear) && !empty($height_rear) && !empty($rim_rear)) {
            $isBundle = 1;
        }

        return $isBundle;
    }

	public function getAttributeValue($attributeCode)
    {
		return $attributeCode;
	}
	
	public function getFilterProductSkus()
    {
		$frontcollection = array();
		$collection = array();
		$sku = array();
		
		if ((count($this->getRearcollection()) > 0) && ($this->isBundle())) {
		$productCollectionFactory = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$productStatus = $this->_objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
		$productVisibility = $this->_objectManager->get('Magento\Catalog\Model\Product\Visibility');
		$request = $this->_objectManager->get('Magento\Framework\App\Request\Http');
		$width_rear                  = $request->getParam('width_rear');
        $height_rear                 = $request->getParam('height_rear');
        $rim_rear                    = $request->getParam('rim_rear');
		
		$width	= $request->getParam('width');
        $height	= $request->getParam('height');
        $rim	= $request->getParam('rim');

		$frontcollection = $productCollectionFactory->create()
                ->addAttributeToSelect('*')
				->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()])
				->setVisibility($productVisibility->getVisibleInSiteIds())
                ->addFieldToFilter('width', $width)
                ->addFieldToFilter('height', $height)
                ->addFieldToFilter('rim', $rim);
				
		$frontIds = array();		
		$rearIds = array();			
		foreach ($frontcollection as $fronProduct) {
			$FrontbrandId   = $fronProduct->getMgsBrand();
			$FrontpatternId = $fronProduct->getPattern();
			$FrontyearId    = $fronProduct->getYear();
			$FrontrunflatId = $fronProduct->getRunflat();

			$rearcollection = $this->getRearcollection();
			foreach ($rearcollection as $rearProduct) {
				if (($FrontbrandId != $rearProduct->getMgsBrand()) || ($fronProduct->getId() == $rearProduct->getId()) || ($FrontpatternId != $rearProduct->getPattern()) || ($FrontyearId != $rearProduct->getYear()) || ($FrontrunflatId != $rearProduct->getRunflat()) || ($fronProduct->getIsSalable() != $rearProduct->getIsSalable())) {
					continue;
				}
				$rearIds[]  = $fronProduct->getId();
				$collection = $productCollectionFactory->create()
						->addAttributeToSelect('*')
						->addFieldToFilter('entity_id', array('in' => $rearIds));
			}
		}
		}
		if (isset($collection) && !empty($collection)) {
            foreach ($collection->getData() as $arr) {
                $sku[] = $arr['sku'];
            }
        }
		//echo '<pre>';print_r($sku);die;
		return $sku;
		
	}
}
