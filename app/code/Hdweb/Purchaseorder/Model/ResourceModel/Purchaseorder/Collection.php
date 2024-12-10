<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	
	protected function _construct()
	{
		$this->_init('Hdweb\Purchaseorder\Model\Purchaseorder', 'Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder');
	}

}
