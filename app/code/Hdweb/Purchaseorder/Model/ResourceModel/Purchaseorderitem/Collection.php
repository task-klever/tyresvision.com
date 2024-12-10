<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	
	
	protected function _construct()
	{
		$this->_init('Hdweb\Purchaseorder\Model\Purchaseorderitem', 'Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem');
	}

}
