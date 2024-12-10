<?php

/**
 * @category   Hdweb
 * @package    Hdweb_Insurance
 * @author     vicky.hdit@gmail.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Hdweb\Insurance\Controller\Index;

use Magento\Framework\App\Action\Context;
use Hdweb\Insurance\Model\InsuranceFactory;

class Save extends \Magento\Framework\App\Action\Action
{
  /**
   * @var Insurance
   */
  protected $_insurance;
  protected $resultJsonFactory;
  protected $insuranceHelper;
  protected $insuranceModel;
  protected $resultRedirectFactory;

  public function __construct(
    Context $context,
    InsuranceFactory $insurance,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Hdweb\Insurance\Helper\Data $insuranceHelper,
    \Hdweb\Insurance\Model\Insurance $insuranceModel,
    \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
  ) {
    $this->_insurance = $insurance;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->insuranceHelper    = $insuranceHelper;
    $this->insuranceModel    = $insuranceModel;
    $this->resultRedirectFactory = $resultRedirectFactory;
    parent::__construct($context);
  }

  public function execute()
  {
    /* $resultRedirect = $this->resultRedirectFactory->create();
    $resultRedirect->setUrl("https://www.tyresvision.com/insurance/index/thirdparty");
    return $resultRedirect; */

    try {
      $postData = $this->getRequest()->getParams();
      if (!empty($postData)) {
        $insurance = $this->_insurance->create();
        /* $insurance->setMake($postData['car_make']);
              $insurance->setModel($postData['car_model']);
              $insurance->setYear($postData['car_model_year']); */
        $insurance->setPolicyType($postData['policy_type']);
        $insurance->setFirstName($postData['customer_name']);
        $insurance->setPhone($postData['mobile_number']);
        $insurance->setEmail($postData['email']);
        /* $insurance->setVehiclePlateNo($postData['vehicle_plate_no']); */

        $insurance->save();
        $this->insuranceHelper->sendEmail($postData);
        //$this->messageManager->addSuccessMessage(__('Insurance details saved successfully.'));
      } else {
        // Handle case when $postData is empty
        // Set error message
        $this->messageManager->addErrorMessage(__('Error: Empty data received.'));
      }
    } catch (\Exception $e) {
      // Handle exceptions here, you can log the exception or set an error message
      $this->messageManager->addErrorMessage(__('An error occurred: %1', $e->getMessage()));
    }

    if ($postData['policy_type'] == 'third-party') {
      $redirectUrl = 'https://tyresvision.insured.ae/car/third-party';
    } else {
      $redirectUrl = 'https://tyresvision.insured.ae/car/comprehensive';
    }

    // Redirect to a specific URL
    $resultRedirect = $this->resultRedirectFactory->create();
    $resultRedirect->setUrl($redirectUrl);
    return $resultRedirect;
  }
}
