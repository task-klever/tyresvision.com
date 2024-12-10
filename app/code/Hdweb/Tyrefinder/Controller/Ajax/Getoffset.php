<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

class Getoffset extends \Magento\Framework\App\Action\Action
{	
	protected $resultJsonFactory;
	protected $productCollectionFactory;
	protected $productFactory;
	
    public function __construct(\Magento\Framework\App\Action\Context $context,
    	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\ProductFactory $productFactory	
	) {
    	$this->resultJsonFactory = $resultJsonFactory;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->productFactory = $productFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
    	$postData = $this->getRequest()->getParams();
		$selectHtml = '';

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory');
		$category = $categoryFactory->create()->load(9);
		
		$collection = $this->productCollectionFactory->create()
					->addAttributeToSelect('*')//;
					->addCategoryFilter($category);
		
		if ($postData['width']) {
            $collection->addAttributeToFilter("width", $postData['width']);
        }

        if ($postData['rim']) {
            $collection->addAttributeToFilter("rim", $postData['rim']);
        }
        
		$collection->setOrder('offset', 'ASC');
        $collection->getSelect()->group('offset');
		
		$attr = $this->productFactory->create()->getResource()->getAttribute('offset');
		$selectHtml = '';
		foreach ($collection as $productData) {
			if ($attr->usesSource()) {
				   $optionText = $attr->getSource()->getOptionText($productData['offset']);
			 }
			 if($productData['offset'] != ''){
			 	
			 	
			 		$selectHtml .= '<li><a href="javascript:void(0)" 
			 		onclick="selectOffset('.$productData['offset'].',\''.$optionText.'\')" id="front-width-'.$productData['offset'].'">'.$optionText.'</a></li>';
			 	
			 }
		}
		$selectHtml .= '</select>';
		$response['status'] = 'SUCCESS';
        $response['response'] = $selectHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}

