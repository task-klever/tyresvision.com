<?php

namespace Mageplaza\Shopbybrand\Model;

use Magento\Framework\Model\AbstractModel;

class Patternmanagement extends AbstractModel
{
	protected function _construct()
	{
		$this->_init('Mageplaza\Shopbybrand\Model\ResourceModel\Patternmanagement');
	}
}