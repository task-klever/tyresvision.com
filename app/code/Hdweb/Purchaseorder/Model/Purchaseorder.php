<?php
namespace Hdweb\Purchaseorder\Model;

class Purchaseorder extends \Magento\Framework\Model\AbstractModel
{


	protected function _construct()
	{
		$this->_init('Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder');
	}

	
}