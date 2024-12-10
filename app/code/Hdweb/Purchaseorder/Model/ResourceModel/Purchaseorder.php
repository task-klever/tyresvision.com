<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel;


class Purchaseorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	
	protected function _construct()
	{
		$this->_init('purchase_order', 'id');
	}
	
}