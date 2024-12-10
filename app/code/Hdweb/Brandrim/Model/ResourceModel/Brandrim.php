<?php
namespace Hdweb\Brandrim\Model\ResourceModel;

class Brandrim extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('installer_brand_rim', 'id');
    }
}
?>