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
		$grandTotal = $cart->getQuote()->getGrandTotal();
        foreach ($result as $key=>$_result) {
			if($grandTotal < 500){
				if ($_result->getCode() == "spotiipay") {
					unset($result[$key]);
				}
			}
        }
        return $result;
    }
}