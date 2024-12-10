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

namespace Ecomteck\StoreLocator\Ui\DataProvider\Stores\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;

class StoresData implements ModifierInterface
{
    /**
     * @var \Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection
     */
    public $collection;

    /**
     * @param CollectionFactory $storesCollectionFactory
     */
    public function __construct(
        CollectionFactory $storesCollectionFactory
    ) {
        $this->collection = $storesCollectionFactory->create();
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $items = $this->collection->getItems();
        /** @var $stores \Ecomteck\StoreLocator\Model\Stores */
        foreach ($items as $stores) {
            $_data = $stores->getData();
            if (isset($_data['image'])) {
                $image = [];
                $image[0]['name'] = $stores->getImage();
                $image[0]['url'] = $stores->getImageUrl();
                $_data['image'] = $image;
            }
            if (isset($_data['details_image'])) {
                $image = [];
                $image[0]['name'] = $stores->getDetailsImage();
                $image[0]['url'] = $stores->getDetailsImageUrl();
                $_data['details_image'] = $image;
            }
            $stores->setData($_data);
            $data[$stores->getId()] = $_data;
        }
        return $data;
    }
}
