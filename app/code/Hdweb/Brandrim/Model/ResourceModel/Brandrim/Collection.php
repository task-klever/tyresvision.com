<?php

namespace Hdweb\Brandrim\Model\ResourceModel\Brandrim;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hdweb\Brandrim\Model\Brandrim', 'Hdweb\Brandrim\Model\ResourceModel\Brandrim');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>