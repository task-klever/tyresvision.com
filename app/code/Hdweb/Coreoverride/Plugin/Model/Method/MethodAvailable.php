<?php

namespace Hdweb\Coreoverride\Plugin\Model\Method;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;

class MethodAvailable
{
    protected $customerSession;
    protected $groupRepository;

    public function __construct(
        Session $customerSession,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param Magento\Payment\Model\MethodList $subject
     * @param $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableMethods(\Magento\Payment\Model\MethodList $subject, $result)
    {
		if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
                foreach ($result as $key=>$_result) {
                    if ($_result->getCode() != "cashondelivery") {
                        unset($result[$key]);
                    }
                }
            }
        }
		
        return $result;
    }
}