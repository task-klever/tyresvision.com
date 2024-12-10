<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\OrdersBase\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use MageWorx\OrdersBase\Model\DeviceData;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->createDeviceInfoTable($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addNameColumns($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addNameColumns(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(DeviceData::TABLE_NAME),
            'device_name',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => 64,
                'nullable' => true,
                'comment' => 'Human-readable Device Name'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable(DeviceData::TABLE_NAME),
            'area_name',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => 64,
                'nullable' => true,
                'comment' => 'Human-readable Area Name'
            ]
        );
    }

    /**
     * Create new table: device info
     *
     * @param SchemaSetupInterface $setup
     */
    private function createDeviceInfoTable(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection()
                       ->newTable(
                           $setup->getTable(DeviceData::TABLE_NAME)
                       )
                       ->addColumn(
                           'entity_id',
                           Table::TYPE_INTEGER,
                           null,
                           [
                               'identity' => true,
                               'unsigned' => true,
                               'nullable' => false,
                               'primary' => true,
                           ],
                           'Id'
                       )
                       ->addColumn(
                           'order_id',
                           Table::TYPE_INTEGER,
                           null,
                           [
                               'unsigned' => true,
                               'nullable' => false,
                           ],
                           'Order ID'
                       )
                       ->addColumn(
                           'device_code',
                           Table::TYPE_INTEGER,
                           3,
                           [],
                           'Device Code'
                       )
                       ->addColumn(
                           'area_code',
                           Table::TYPE_INTEGER,
                           2,
                           [],
                           'Area Code'
                       )
                       ->addIndex(
                           $setup->getIdxName(
                               DeviceData::TABLE_NAME,
                               ['order_id']
                           ),
                           ['order_id'],
                           ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                       )
                       ->addIndex(
                           $setup->getIdxName(
                               DeviceData::TABLE_NAME,
                               ['device_code']
                           ),
                           ['device_code']
                       )
                       ->addIndex(
                           $setup->getIdxName(
                               DeviceData::TABLE_NAME,
                               ['area_code']
                           ),
                           ['area_code']
                       )
                       ->setComment('Customers Device Data Table');

        $setup->getConnection()->createTable($table);
    }
}
