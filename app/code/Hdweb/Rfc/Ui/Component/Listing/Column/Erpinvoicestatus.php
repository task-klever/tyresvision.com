<?php
namespace Hdweb\Rfc\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class Erpinvoicestatus extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $order  = $this->_orderRepository->get($item["entity_id"]);
                $status = $order->getData("erp_invoice_status");

                switch ($status) {
                    case "0":
                        $erp_invoice_status = "No";
                        break;
                    case "1";
                        $erp_invoice_status = "Yes";
                        break;
                    default:
                        $erp_invoice_status = "No";
                        break;

                }

                // $this->getData('name') returns the name of the column so in this case it would return erp_invoice_status
                $item[$this->getData('name')] = $erp_invoice_status;
            }
        }

        return $dataSource;
    }
}