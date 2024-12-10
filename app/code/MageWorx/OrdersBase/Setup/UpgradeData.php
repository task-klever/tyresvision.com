<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\OrdersBase\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use MageWorx\OrdersBase\Model\DeviceData;
use MageWorx\OrdersBase\Model\DeviceTypeParser;
use MageWorx\OrdersBase\Model\AreaCodeParser;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @inheritDoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->transferCodesToNameColumns($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function transferCodesToNameColumns(ModuleDataSetupInterface $setup): void
    {
        $connection = $setup->getConnection();

        // Area
        $areas = AreaCodeParser::getAllAreas();
        foreach ($areas as $code => $name) {
            $where = ['area_code = ?' => $code];
            $connection->update(
                $setup->getTable(DeviceData::TABLE_NAME),
                [
                    'area_name' => $name
                ],
                $where
            );
        }

        // Device
        $devices = DeviceTypeParser::getAllDevices();
        foreach ($devices as $name => $code) {
            $name = ucwords($name);
            $where = ['device_code = ?' => $code];
            $connection->update(
                $setup->getTable(DeviceData::TABLE_NAME),
                [
                    'device_name' => $name
                ],
                $where
            );
        }
    }
}
