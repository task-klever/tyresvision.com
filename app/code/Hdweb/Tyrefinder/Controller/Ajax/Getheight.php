<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

class Getheight extends \Magento\Framework\App\Action\Action
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
        $attributeCode = 'width';
        $attributeValueId = $postData['width'];
        $selectHtml = '';
		$response = array();
		
		$collection = $this->productCollectionFactory->create()
					->addAttributeToSelect('height')
					->addAttributeToFilter($attributeCode, $attributeValueId);
		$collection->setOrder('height', 'ASC');
        $collection->getSelect()->group('height');
		
		$attr = $this->productFactory->create()->getResource()->getAttribute('height');
		
		foreach ($collection as $productData) {
			if ($attr->usesSource()) {
				   $optionText = $attr->getSource()->getOptionText($productData['height']);
			 }
		
			if(!empty($productData['height'])) { 
				if($type == 'front'){
					$optionText=strtolower($optionText);
					if($optionText == 'none') {
						$optionText=ucfirst($optionText);
						$selectHtml .= '<li><a href="javascript:void(0)" onclick="getrim('.$productData['height'].',\''.$optionText.'\',\'front\')" id="rear-height-'.$productData['height'].'">'.$optionText.'</a></li>';
					}else{
				 	$selectHtml .= '<li><a href="javascript:void(0)" 
				 	onclick="getrim('.$productData['height'].',\''.$optionText.'\',\'front\')" id="rear-height-'.$productData['height'].'">'.$optionText.'</a></li>';
				    }
				}
				else{
					$optionText=strtolower($optionText);
					if($optionText == 'none') {
						$optionText=ucfirst($optionText);
						$selectHtml .= '<li><a href="javascript:void(0)" onclick="getRearrim('.$productData['height'].',\''.$optionText.'\',\'rear\')" id="rear-height-'.$productData['height'].'">'.$optionText.'</a></li>';
					}else{
				 	$selectHtml .= '<li><a href="javascript:void(0)" 
				 	onclick="getRearrim('.$productData['height'].',\''.$optionText.'\',\'rear\')" id="rear-height-'.$productData['height'].'">'.$optionText.'</a></li>';
				    }	
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

