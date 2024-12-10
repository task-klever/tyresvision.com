<?php
namespace Hdweb\Brandrim\Block\Adminhtml\Brandrim\Edit;

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
        $this->setId('brandrim_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Brandrim Information'));
    }
}