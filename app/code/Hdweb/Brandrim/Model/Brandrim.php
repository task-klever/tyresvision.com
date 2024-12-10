<?php
namespace Hdweb\Brandrim\Model;

class Brandrim extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hdweb\Brandrim\Model\ResourceModel\Brandrim');
    }
}
?>