<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\GoogleShoppingFeed\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class DynamicRow
 */
class DynamicRow extends AbstractFieldArray
{
    /**
     * @var GoogleTagColumn
     */
    private $tagRenderer;

    /**
     * @var ProductAttributesColumn
     */
    private $attributeRenderer;

    /**
     * @var ProductAttributesColumn
     */
    private $attrEnable;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('tag', [
            'style' => 'width:170px',
            'label' => __('Google Attribute'),
            'renderer' => $this->getTagRenderer()
        ]);
        $this->addColumn('attr', [
            'label' => __('Magento Attribute'),
            'class' => 'required-entry',
            'style' => 'width:200px',
            'renderer' => $this->getAttributesRenderer()
        ]);
        $this->addColumn('value', ['label' => __('Own Value'), 'style' => 'width:170px']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $tag = $row->getTag();
        if ($tag !== null) {
            $options['option_' . $this->getTagRenderer()->calcOptionHash($tag)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return GoogleTagColumn
     * @throws LocalizedException
     */
    private function getTagRenderer()
    {
        if (!$this->tagRenderer) {
            $this->tagRenderer = $this->getLayout()->createBlock(
                GoogleTagColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->tagRenderer;
    }

    /**
     * @return BlockInterface|ProductAttributesColumn
     * @throws LocalizedException
     */
    private function getAttributesRenderer()
    {
        if (!$this->attributeRenderer) {
            $this->attributeRenderer = $this->getLayout()->createBlock(
                ProductAttributesColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributeRenderer;
    }

    /**
     * @return BlockInterface|ProductAttributesColumn
     * @throws LocalizedException
     */
    private function getEnableRenderer()
    {
        if (!$this->attrEnable) {
            $this->attrEnable = $this->getLayout()->createBlock(
                Checkbox::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attrEnable;
    }
}
