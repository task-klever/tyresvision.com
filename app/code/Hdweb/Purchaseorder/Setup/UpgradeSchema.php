<?php

namespace Hdweb\Purchaseorder\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'vatApplicable',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 1,
                    'comment'  => 'VAT Applicable',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'created_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment'  => 'Vendor Created At',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'create_by',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Created By',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'update_by',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Updated By',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'created_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment'  => 'Created Date',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'updated_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                    'comment'  => 'Updated Date',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'email_copy',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'Vendor Send copy mail',
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'po_grandtotal',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'po_grandtotal',
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'po_margin',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'po_margin',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'po_marginperc',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'po_marginperc',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'vendor_id',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'vendor_id',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'vendor_name',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'vendor_name',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'order_id',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'order_id',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'created_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'created_at',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'tyre_description',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'tyre_description ',
                ]
            );
        }

        $installer->endSetup();
    }

}
