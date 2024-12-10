<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
namespace Ecomteck\StoreLocator\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Installertype extends AbstractSource implements ArrayInterface
{
    /**
     * get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '', 'label' => 'Select Installer Type'],
            ['value' => 'premium_installer.png', 'label' => 'Premium'],
            ['value' => 'independent_installer.png', 'label' => 'Independent'],
            ['value' => 'mobile_installer.png', 'label' => 'Mobile']
        ];

    }


}
