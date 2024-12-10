<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Hdweb\Shippingform\Controller\Ajax;

/*use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;*/

class Getvehiclemodel extends \Magento\Framework\App\Action\Action
{

    protected $helper;
    protected $resultJsonFactory;
    protected $cartModel;
    protected $customerRepository;
    protected $corehelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Hdweb\Core\Helper\Data $corehelper
    ) {
        parent::__construct($context);
        $this->helper              = $helper;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->_cartModel          = $cartModel;
        $this->_customerRepository = $customerRepository;
        $this->corehelper          = $corehelper;
    }

    public function execute()
    {

        $vehiclemakes = $this->helper->jsonDecode($this->getRequest()->getContent());
        if ($vehiclemakes) {
            try {
                $wheelApiKey       = $this->corehelper::WHEEL_SEARCH_APIKEY;
                $wheelApiURL       = $this->corehelper::WHEEL_SEARCH_URL;
                $VehicleModel_url  = $wheelApiURL . "?user_key=" . $wheelApiKey . "&make=" . $vehiclemakes. "&region=medm";;
                $WheelVehicleModel = file_get_contents($VehicleModel_url);
                $WheelVehicleModel = json_decode($WheelVehicleModel);
                $modelSelectHtml   = '<option value="">' . __('Model') . '</option>';
                $yearSelectHtml    = '<option value="">' . __('Year') . '</option>';
                //foreach ($WheelVehicleModel as $key => $value) {
                foreach ($WheelVehicleModel->data as $key => $value) {
                    $modelSelectHtml .= '<option value="' . $value->slug . '">' . $value->name . '</option>';

                }
                $response[] = [
                    'vehiclemodel' => $modelSelectHtml,
                    'vehicleyear'  => $yearSelectHtml,
                ];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);

            } catch (Exception $ex) {

            }
        }
    }
}
