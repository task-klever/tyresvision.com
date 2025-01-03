<?php
namespace Hdweb\Rfc\Block\Adminhtml\Rfc\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rfc_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rfc Information'));
    }
}