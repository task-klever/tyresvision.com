<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Model\Carrier;

use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * @category   Ecomteck
 * @package    Ecomteck_StorePickup
 * @author     ecomteck@gmail.com
 * @website    http://www.ecomteck.com
 */
class StorePickup extends AbstractCarrier implements CarrierInterface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'storepickup';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory
     */
    protected $collectionFactory;

    protected $storelocator;

    protected $_checkoutSession;

    protected $productRepository;

    protected $installerbrandrimFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory $collectionFactory,
        \Ecomteck\StoreLocator\Model\StoresFactory $storelocator,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Hdweb\Tyrefinder\Model\InstallerbrandrimFactory $installerbrandrimFactory,
        array $data = []
    ) {
        $this->_rateResultFactory       = $rateResultFactory;
        $this->_rateMethodFactory       = $rateMethodFactory;
        $this->collectionFactory        = $collectionFactory;
        $this->storelocator             = $storelocator;
        $this->_checkoutSession         = $_checkoutSession;
        $this->productRepository        = $productRepository;
        $this->installerbrandrimFactory = $installerbrandrimFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * Collect and get rates for storefront
     *
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request)
    {
        /**
         * Make sure that Shipping method is enabled
         */
        if (!$this->isActive()) {
            return false;
        }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        $pickup_store           = $this->_checkoutSession->getQuote()->getPickupStore();
        $custom_shipping_amount = "";
        $BrandRimShippingPrice  = array();
        if (isset($pickup_store) && !empty($pickup_store) && $pickup_store > 0) {
            $allstores = $this->storelocator->create()->getCollection()->addFieldToFilter('stores_id', $pickup_store);
            if (count($allstores) > 0) {
                $custom_shipping_amount = $allstores->getFirstItem()->getShippingAmount();

            }

            $allQuoteItem                = $this->_checkoutSession->getQuote()->getAllItems();
            $installerbrandrimObj        = $this->installerbrandrimFactory->create();
            $installerbrandrimCollection = $installerbrandrimObj->getCollection()->addFieldToFilter('installerid', $pickup_store); //Get Collection of module data
            $installerRimBrandArray      = $installerbrandrimCollection->getData();

            foreach ($allQuoteItem as $key => $item) {
                $productId      = $item->getProductId();
                $productObj     = $this->productRepository->getById($productId);
                $productQty     = $item->getQty();
                $productBrandId = $productObj->getMgsBrand();
                $prodcutRimId   = $productObj->getRim();

                foreach ($installerRimBrandArray as $key => $installerbrand) {
                    $installerBrandId        = $installerbrand['brand'];
                    $installerRimId          = $installerbrand['rim'];
                    $installerQty            = $installerbrand['qty'];
                    $installerShipping_mount = $installerbrand['shipping_amount'];

                    if ($productBrandId == $installerBrandId) {
                        if ($prodcutRimId == $installerRimId) {
                            if ($productQty >= $installerQty) {
                                $BrandRimShippingPrice[] = $installerShipping_mount;
                            }
                        }
                    }
                }
                // $writer     = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
                // $logger     = new \Zend\Log\Logger();
                // $logger->addWriter($writer);
                // $logger->info("id--" . $productId . "--Info" . $item->getName() . '--Brand-' . $productObj->getMgsBrand() . '--Rim-' . $productObj->getRim() . '--' . $qty);

            }

        }

        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog3.log');
        // $logger = new \Zend\Log\Logger();
        // $logger->addWriter($writer);
        // $logger->info(print_r($BrandRimShippingPrice, true));

        if (count($BrandRimShippingPrice) > 0) {
            $custom_shipping_amount = max($BrandRimShippingPrice);
        }

        if ($this->isAvailable($request)) {
            $shippingPrice = $this->getConfigData('price');

            $method = $this->_rateMethodFactory->create();

            /**
             * Set carrier's method data
             */
            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));

            /**
             * Displayed as shipping method under Carrier
             */
            $method->setMethod($this->getCarrierCode());
            $method->setMethodTitle($this->getConfigData('name'));

            if (isset($custom_shipping_amount) && !empty($custom_shipping_amount)) {
                $method->setPrice($custom_shipping_amount);
                $method->setCost($custom_shipping_amount);
            } else {
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
            }

            $result->append($method);
        } else {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $errorMsg = $this->getConfigData('specificerrmsg');
            $error->setErrorMessage(__(
                $errorMsg
            )
            );
            return $error;
        }

        return $result;
    }

    protected function isAvailable($request)
    {
        $productIds = [];
        foreach ($request->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
        }
        $collection = $this->collectionFactory->create();
        $collection->addActiveFilter()->addProductsFilter($productIds);
        if ($collection->getSize() > 0) {
            return true;
        }
        return false;
    }
}
