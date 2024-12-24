<?php

namespace Hdweb\Installer\Plugin\Model\Method;

class MethodAvailable
{
    /**
     * @param Magento\Payment\Model\MethodList $subject
     * @param $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableMethods(\Magento\Payment\Model\MethodList $subject, $result)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 

        $quoteItems = $cart->getQuote()->getItems();

        $hasHankookProduct = false;

        foreach ($quoteItems as $item) {
            $product = $item->getProduct();
            $brand = $product->getAttributeText('mgs_brand'); // Assuming 'mgs_brand' is the attribute code for brand 

            if ($brand === 'Hankook') {
                $hasHankookProduct = true;
                break;
            }
        }

        if ($hasHankookProduct) {
            foreach ($result as $key => $_result) {
                if ($_result->getCode() !== "cashondelivery") {
                    unset($result[$key]);
                }
            }
        } else {
            $grandTotal = $cart->getQuote()->getGrandTotal();
            foreach ($result as $key => $_result) {
                if ($grandTotal < 500 && $_result->getCode() == "spotiipay") {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }
}