<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

class Getrimforoffset extends \Magento\Framework\App\Action\Action
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
    	$type = $postData['type'];
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
        
		$collection->setOrder('rim', 'ASC');
        $collection->getSelect()->group('rim');
		
		$attr = $this->productFactory->create()->getResource()->getAttribute('rim');
		$selectHtml = '';
		foreach ($collection as $productData) {
			if ($attr->usesSource()) {
				   $optionText = $attr->getSource()->getOptionText($productData['rim']);
			 }
			 if($productData['rim'] != ''){
			 	
			 	if($type == 'front')
			 	{
			 		$selectHtml .= '<li><a href="javascript:void(0)" 
			 		onclick="getoffset('.$productData['rim'].',\''.$optionText.'\')" id="front-width-'.$productData['rim'].'">'.$optionText.'</a></li>';
			 	}
			 	else{
			 	  $selectHtml .= '<li><a href="javascript:void(0)" 
			 		onclick="getoffset('.$productData['rim'].',\''.$optionText.'\')" id="rear-width-'.$productData['rim'].'">'.$optionText.'</a></li>';
			 	}
			 }
		}
		$selectHtml .= '</select>';
		$response['status'] = 'SUCCESS';
        $response['response'] = $selectHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}

