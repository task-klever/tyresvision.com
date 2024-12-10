<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        $pickupDate    = $extAttributes->getPickupDate();
        $pickupTime    = $extAttributes->getPickupTime();
        $pickupStore   = $extAttributes->getPickupStore();

        $plate = ucwords($extAttributes->getPlate());
        $make  = ucwords($extAttributes->getMake());
        $model = ucwords($extAttributes->getModel());
        $year  = ucwords($extAttributes->getYear());

        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setPickupDate($pickupDate);
        $quote->setPickupTime($pickupTime);
        $quote->setPickupStore($pickupStore);

        $quote->setPlate($plate);
        $quote->setMake($make);
        $quote->setModel($model);
        $quote->setYear($year);
    }
}
