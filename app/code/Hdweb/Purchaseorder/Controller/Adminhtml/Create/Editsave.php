<?php

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Editsave extends \Magento\Backend\App\Action
{

    protected $resultPagee;
    protected $purchaseorder;
    protected $purchaseorderitem;
    protected $povendor;
    protected $order;
    protected $scopeConfig;
    protected $productRepository;
    protected $pricehelper;
    protected $_filesystem;
    protected $fileFactory;
    protected $authSession;
    protected $orderInterfaceFactory;
    protected $orderItemFactory;
    protected $pohelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Hdweb\Purchaseorder\Model\PurchaseorderFactory $purchaseorder,
        \Hdweb\Purchaseorder\Model\PurchaseorderitemFactory $purchaseorderitem,
        \Hdweb\Purchaseorder\Model\Povendor $povendor,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricehelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        Session $authSession,
        \Hdweb\Purchaseorder\Helper\Data $pohelper

    ) {
        parent::__construct($context);
        $this->resultPageFactory     = $resultPageFactory;
        $this->purchaseorder         = $purchaseorder;
        $this->purchaseorderitem     = $purchaseorderitem;
        $this->povendor              = $povendor;
        $this->order                 = $order;
        $this->scopeConfig           = $scopeConfig;
        $this->productRepository     = $productRepository;
        $this->pricehelper           = $pricehelper;
        $this->_filesystem           = $filesystem;
        $this->fileFactory           = $fileFactory;
        $this->authSession           = $authSession;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderItemFactory      = $orderItemFactory;
        $this->pohelper              = $pohelper;
    }

    public function execute()
    {

        $data           = $this->_request->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $submit_param   = $data['submit'];
        if (isset($submit_param) && $submit_param == 'pdf') {
            $sendEmailpdf = $this->sendmailpdf();
            $this->messageManager->addSuccess(__('Purchase order email has been sent succesfully'));
            return $resultRedirect->setPath('*/*/grid');
        } else if (isset($submit_param) && $submit_param == 'download') {
            $sendEmailpdf = $this->sendmailpdf();
            $this->messageManager->addSuccess(__('Purchasee order has been downloaded'));
            return $resultRedirect->setPath('*/*/grid');
        } else if (isset($submit_param) && $submit_param == 'delete') {
            if (isset($data['poid'])) {
                //
                $purchaseorder_model = $this->purchaseorder->create();
                $purchaseorder_model->load($data['poid'], 'id');
                $purchaseorder_model->delete();
                $ispurchaseorderdone = 0;
                if (isset($data['item']) && count($data['item']) > 0) {
                    $model                       = $this->purchaseorderitem->create();
                    $purchaseorderitemcollection = $this->purchaseorderitem->create()->getCollection()->addFieldToFilter('poid', $data['poid']);

                    foreach ($purchaseorderitemcollection as $modeldata) {
                        $ispurchaseorderdone = 1;
                        $model->load($modeldata['id'], 'id');
                        $model->delete();
                    }
                }

                if ($ispurchaseorderdone) {
                    $this->pohelper->savePoGrandTotal($data['orderreference_no']);
                }
                $this->messageManager->addSuccess(__('Your purchase order has been deleted succesfully'));
                return $resultRedirect->setPath('*/*/grid');
            }
        } else {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            if (isset($data['item']) && isset($data['poid'])) {

                if (isset($data['grandtotal']) && count($data['item']) > 0 && $data['grandtotal'] > 0) {
                    $itemtemplate        = "";
                    $ispurchaseorderdone = 0;

                    $purchaseorder_model = $this->purchaseorder->create();
                    $purchaseorder_model->load($data['poid'], 'id');
                    $purchaseorder_model->setPoreferenceNo($data['poreference_no']);
                    $purchaseorder_model->setOrderreferenceNo($data['orderreference_no']);
                    $purchaseorder_model->setVendor($data['vendor']);
                    $purchaseorder_model->setSubtotal($data['subtotal']);
                    $purchaseorder_model->setVat($data['vat']);
                    $purchaseorder_model->setGrandtotal($data['grandtotal']);
                    $purchaseorder_model->setComment($data['comment']);
                    $purchaseorder_model->setUpdateBy($this->authSession->getUser()->getId());
                    $purchaseorder_model->save();
                    $last_po_id = $purchaseorder_model->getId();

                    if (count($data['item']) > 0) {

                        $model = $this->purchaseorderitem->create();

                        $purchaseorderitemcollection = $this->purchaseorderitem->create()->getCollection()->addFieldToFilter('poid', $data['poid']);

                        foreach ($purchaseorderitemcollection as $modeldata) {

                            $model->load($modeldata['id'], 'id');
                            $model->delete();
                        }

                        $vendoritemid   = $data['vendor'];
                        $itemcollection = $this->povendor->getCollection();
                        $itemcollection->addFieldToFilter('id', array('eq' => $vendoritemid));
                        $vendoritem_name = "";
                        if ($itemcollection->getSize()) {
                            $vendoritem_data = $itemcollection->getFirstItem();
                            $vendoritem_name = $vendoritem_data->getName();
                        }

                        foreach ($data['item'] as $key => $value) {

                            $CreatedAt    = date('Y-m-d h:i:s', time());
                            $product_objs = $this->productRepository->get($value['sku']);

                            $ispurchaseorderdone     = 1;
                            $purchaseorderitem_model = $this->purchaseorderitem->create();
                            $purchaseorderitem_model->setPoid($last_po_id);
                            $purchaseorderitem_model->setSku($value['sku']);
                            $purchaseorderitem_model->setPrice($value['price']);
                            $purchaseorderitem_model->setQty($value['qty']);

                            $purchaseorderitem_model->setVendorId($data['vendor']);
                            $purchaseorderitem_model->setVendorName($vendoritem_name);
                            $purchaseorderitem_model->setOrderId($data['orderreference_no']);
                            $purchaseorderitem_model->setCreatedAt($CreatedAt);
                            $purchaseorderitem_model->setTyreDescription($product_objs->getName());

                            $rowtotal = trim($value['price']) * (int) $value['qty'];
                            $rowtotal = number_format($rowtotal, 2);
                            $rowtotal = str_replace(',', '', $rowtotal);

                            $purchaseorderitem_model->setRowtotal($rowtotal);
                            $purchaseorderitem_model->save();
                            $purchaseorderitem_model->unsetData();

                            //for email template
                            //$product_obj=$this->productRepository->get($value['sku']);

                            $orderref   = $this->orderInterfaceFactory->create()->loadByIncrementId($data['orderreference_no']);
                            $orderitems = $this->orderItemFactory->create()->getCollection()->addFieldToFilter('order_id', array('eq' => $orderref->getEntityId()))->addFieldToFilter('sku', array('eq' => $value['sku']))->getFirstItem();

                            $itemtemplate .= '<tr>
                                                       <td colspan="3"><span style="padding-top:5px;font-weight:500;">
                                                       <br>' . $orderitems->getShortDescription() . '</span><br>
                                                          SKU: ' . $value['sku'] . ' </span>
                                                       </td>
                                                            <td style="text-align:center;font-size:14px;">' . $this->pricehelper->currency($value['price'], true, false) . '</td>
                                                            <td style="text-align:center;font-size:14px;">' . $value['qty'] . '</td>
                                                            <td style="text-align:center;font-size:14px;">
                                                                <span class="price">' . $this->pricehelper->currency($rowtotal, true, false) . '</span>
                                                            </td>
                                                  </tr>';
                        }
                    }

                    if ($ispurchaseorderdone) {
                        $this->pohelper->savePoGrandTotal($data['orderreference_no']);
                    }

                    /*email done */
                    $this->messageManager->addSuccess(__('Your purchase order has been edited successfully'));
                    //$RefererUrl = $this->_redirect->getRefererUrl();
                    //return $resultRedirect->setPath($RefererUrl);
                    return $resultRedirect->setPath('*/*/grid');
                } else {
                    $this->messageManager->addError(__('Faild to create purchase order.'));
                    /* $RefererUrl = $this->_redirect->getRefererUrl();
                    return $resultRedirect->setPath($RefererUrl); */
                    return $resultRedirect->setPath('*/*/grid');
                }
            } else {
                /*email done */
                $this->messageManager->addError(__('No any product item found.'));
                /* $RefererUrl = $this->_redirect->getRefererUrl();
                return $resultRedirect->setPath($RefererUrl); */
                return $resultRedirect->setPath('*/*/grid');
            }
        }
    }

    public function sendmailpdf()
    {

        /* Send email*/
        $data           = $this->_request->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $itemtemplate   = "";
        $model          = $this->purchaseorderitem->create();

        $purchaseorderitemcollection = $this->purchaseorderitem->create()->getCollection()->addFieldToFilter('poid', $data['poid']);

        foreach ($data['item'] as $key => $value) {

            $rowtotal = trim($value['price']) * (int) $value['qty'];
            $rowtotal = number_format($rowtotal, 2);
            $rowtotal = str_replace(',', '', $rowtotal);

            //for email template
            //$product_obj = $this->productRepository->get($value['sku']);
            try {
                $product_obj = $this->productRepository->get($value['sku']);
                // Product exists, you can proceed with your logic here
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // Product doesn't exist, handle the error or take necessary actions
                // For example, you can log the error, set a default product, or do nothing depending on your requirements
                $product_obj = null; // or any default value you want
            }
            if ($product_obj == null) {
                $productName = 'Product not found';
            } else {
                $productName = $product_obj->getName();
            }

            $itemtemplate .= '<tr>
                                                       <td colspan="3" style="font-family: Montserrat, sans-serif;"><span style="font-family: Montserrat, sans-serif;padding-top:5px;font-weight:500;">
                                                       <br>' . $productName . '</span><br>
                                                          SKU: ' . $value['sku'] . ' </span>
                                                       </td>
                                                            <td style="font-family: Montserrat, sans-serif;text-align:center;font-size:14px;">' . $this->pricehelper->currency($value['price'], true, false) . '</td>
                                                            <td style="font-family: Montserrat, sans-serif;text-align:center;font-size:14px;">' . $value['qty'] . '</td>
                                                            <td style="font-family: Montserrat, sans-serif;text-align:right;font-size:14px;">
                                                                <span class="price" style="font-family: Montserrat, sans-serif;">' . $this->pricehelper->currency($rowtotal, true, false) . '</span>
                                                            </td>
                                                  </tr>';
        }
        /* Send email*/

        $vendorid   = $data['vendor'];
        $collection = $this->povendor->getCollection();
        $collection->addFieldToFilter('id', array('eq' => $vendorid));

        if ($collection->getSize()) {
            $vendor_data = $collection->getFirstItem();
            $vendor_name          = $vendor_data->getName();
            $vendor_contactperson = $vendor_data->getContactPerson();
            $vendor_email         = $vendor_data->getEmail();
            $vendor_copy_email    = $vendor_data->getEmailCopy();
            $vendor_phone         = $vendor_data->getPhone();
            $vendor_address       = $vendor_data->getAddress();
            $vendor_city          = $vendor_data->getCity();

            $bill_to = $vendor_name . "<br>" . $vendor_address . "<br>" . $vendor_contactperson . "<br>Tel: " . $vendor_phone . "<br>Email: " . $vendor_email;

            $ordercomment = $data['comment'];

            $poreference_no = $data['poreference_no'];

            $order_incrementid = $data['orderreference_no'];
            $order = $this->order->loadByIncrementId($order_incrementid);

            $addressConfig = $objectManager->get('Magento\Customer\Model\Address\Config');
            $installer_id = $order->getPickupStore();

            if ($installer_id != 0) {
                $installer_id = $installer_id;
            } else {
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            }
            $installerobj = $objectManager->create('Ecomteck\StoreLocator\Model\Stores')->load($installer_id);
            //echo '<pre>';print_r($installerobj->getData());die;

            $shipingAddress     = $order->getShippingAddress();
            $renderer             = $addressConfig->getFormatByCode('html')->getRenderer();
            //$shippingAddressHtml = strip_tags($renderer->renderArray($shipingAddress));
            if (isset($installerobj['name'])) {
                $shippingAddressHtml1 = $installerobj['name'] . "<br>" .
                    $installerobj['address'] . "<br>" .
                    $installerobj['city'] . "<br>Tel: " .
                    $installerobj['phone'] . "<br>Email: " .
                    $installerobj['email'];
                $shippingAddressHtml = $installerobj['name'] .
                    $installerobj['address'] .
                    $installerobj['city'] .
                    $installerobj['phone'] .
                    $installerobj['email'];
            }

            $podate           = date("d/m/Y");
            $itemtable = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<thead class="thead-dark" style="font-family: Montserrat, sans-serif;background-color:#4bcf2e;border-bottom-color:#4bcf2e;color:white;">
										  <tr>
											<th scope="col" colspan="3" style="font-family: Montserrat, sans-serif;font-weight: 500;font-size: 12px;padding:3px 9px;">ITEM</th>
											<th scope="col" style="font-family: Montserrat, sans-serif;text-align:center;font-weight: 500;font-size: 12px;padding:3px 9px;">PRICE</th>
											<th scope="col" style="font-family: Montserrat, sans-serif;text-align:center;font-weight: 500;font-size: 12px;padding:3px 9px;">QTY</th>
											<th scope="col" style="font-family: Montserrat, sans-serif;text-align:right;font-weight: 500;font-size: 12px;padding:3px 9px;">TOTAL</th>
										  </tr>
										</thead>
									   <tbody>' . $itemtemplate . '</tbody>
										<tfoot class="order-totals">
											<tr class="subtotal" style="font-family: Montserrat, sans-serif;text-align: right;background: #fff;">
												<td colspan="5" scope="row" style="font-family: Montserrat, sans-serif;background: #fff !important;">
																Sub Total
												</td>
												<td data-td="Sub Total" style="font-family: Montserrat, sans-serif;text-align: right;background: #fff !important;">
														 <span class="price">' . $this->pricehelper->currency($data['subtotal'], true, false) . '</span>
												</td>
											</tr>

											<tr class="totals-tax">
												<td colspan="5" scope="row" style="font-family: Montserrat, sans-serif;background: #fff !important;text-align:right;">
																VAT 5%            </td>
												<td data-th="VAT 5%" style="font-family: Montserrat, sans-serif;background: #fff !important;text-align:right;">
													<span class="price" style="font-family: Montserrat, sans-serif;">' . $this->pricehelper->currency($data['vat'], true, false) . '</span>    </td>
											</tr>


										  <tr class="grand_total" style="text-align: right;background: #fff;">
												 <td colspan="5" scope="row" style="font-family: Montserrat, sans-serif;background: #fff !important;">
															Grand Total
												 </td>
												<td data-td="Grand Total" style="font-family: Montserrat, sans-serif;text-align: right;background: #fff !important;">
															<span class="price">' . $this->pricehelper->currency($data['grandtotal'], true, false) . '</span>
												 </td>
										  </tr>
									</tfoot>
							</table>';

            /* Create PDF */
            $pdflogoName            = $this->scopeConfig->getValue('purchaseorder/general/po_logo_name', ScopeInterface::SCOPE_STORE);
            $pdfstoreName           = $this->scopeConfig->getValue('purchaseorder/general/po_store_name', ScopeInterface::SCOPE_STORE);
            $pdfStoreaddressStreet1 = $this->scopeConfig->getValue('purchaseorder/general/po_store_address_street1', ScopeInterface::SCOPE_STORE);
            $pdfStoreaddressStreet2 = $this->scopeConfig->getValue('purchaseorder/general/po_store_address_street2', ScopeInterface::SCOPE_STORE);
            $pdfTrnno               = $this->scopeConfig->getValue('purchaseorder/general/po_trn_no', ScopeInterface::SCOPE_STORE);
            $pdfPhoneno             = $this->scopeConfig->getValue('purchaseorder/general/po_phone_no', ScopeInterface::SCOPE_STORE);
            $pdfWebsiteName         = $this->scopeConfig->getValue('purchaseorder/general/po_website', ScopeInterface::SCOPE_STORE);
            $pdfContactPerson       = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person', ScopeInterface::SCOPE_STORE);
            $pdfContactPersonPhone  = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person_phone_no', ScopeInterface::SCOPE_STORE);
            $pdfContactPersonEmail  = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person_email', ScopeInterface::SCOPE_STORE);
            $pdfFileName            = $this->scopeConfig->getValue('purchaseorder/general/po_file_name', ScopeInterface::SCOPE_STORE);
            $pdf                    = new \Zend_Pdf();
            $pdf->pages[]           = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
            $page                   = $pdf->pages[0]; // this will get reference to the first page.
            $style                  = new \Zend_Pdf_Style();
            $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
            $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
            $style->setFont($font, 15);
            $page->setStyle($style);
            $width        = $page->getWidth();
            $hight        = $page->getHeight();
            $x            = 30;
            $pageTopalign = 850; //default PDF page height
            $this->y      = 850 - 170; //print table row from page top – 100px
            //Draw table header row’s
            $style->setFont($font, 16);
            $page->setStyle($style);

            $imagePath = 'logo.png';
            if ($pdflogoName != '') {
                $imagePath = $pdflogoName;
            }

            $storeName          = '';
            $storeAddress1      = '';
            $storeAddress2      = '';
            $trnNo              = '';
            $phoneNo            = '';
            $website            = '';
            $contactPerson      = '';
            $contactPersonPhone = '';
            $contactPersonEmail = '';
            $pdfFile            = 'PO-';

            if ($pdfstoreName != '') {
                $storeName = $pdfstoreName;
            }

            if ($pdfStoreaddressStreet1 != '') {
                $storeAddress1 = $pdfStoreaddressStreet1;
            }

            if ($pdfStoreaddressStreet2 != '') {
                $storeAddress2 = $pdfStoreaddressStreet2;
            }

            if ($pdfTrnno != '') {
                $trnNo = $pdfTrnno;
            }
            if ($pdfPhoneno != '') {
                $phoneNo = $pdfPhoneno;
            }
            if ($pdfWebsiteName != '') {
                $website = $pdfWebsiteName;
            }
            if ($pdfContactPerson != '') {
                $contactPerson = $pdfContactPerson;
            }
            if ($pdfContactPersonPhone != '') {
                $contactPersonPhone = $pdfContactPersonPhone;
            }
            if ($pdfContactPersonEmail != '') {
                $contactPersonEmail = $pdfContactPersonEmail;
            }
            if ($pdfFileName != '') {
                $pdfFile = $pdfFileName;
            }

            $image = "";
            if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath));
            }

            $y1 = 800;
            $y2 = 830;
            $x1 = 400;
            $x2 = 530;

            $page->drawImage($image, $x1, $y1, $x2, $y2);

            $page->drawRectangle(30, $this->y - 20, $page->getWidth() - 30, $this->y + 110, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $style->setFont($font, 12);
            $page->setStyle($style);
            $page->drawText(__($storeName), $x + 5, $this->y + 90, 'UTF-8');
            $style->setFont($font, 12);
            $page->setStyle($style);
            $page->drawText(__($storeAddress1), $x + 5, $this->y + 75, 'UTF-8');
            $page->drawText(__($storeAddress2), $x + 5, $this->y + 60, 'UTF-8');
            $page->drawText(__("TRN: " . $trnNo), $x + 5, $this->y + 45, 'UTF-8');
            $page->drawText(__("Phone: " . $phoneNo), $x + 5, $this->y + 30, 'UTF-8');
            $page->drawText(__("Website: " . $website), $x + 5, $this->y + 15, 'UTF-8');

            $page->drawText(__("PURCHASE ORDER"), $x + 350, $this->y + 90, 'UTF-8');
            $page->drawText(__("DATE"), $x + 350, $this->y + 75, 'UTF-8');
            $page->drawText(__("PO#"), $x + 350, $this->y + 60, 'UTF-8');

            //Po value
            $page->drawText(date("d/m/Y"), $x + 450, $this->y + 75, 'UTF-8');
            $page->drawText($data['orderreference_no'], $x + 450, $this->y + 60, 'UTF-8');
            //$page->drawText($data['orderreference_no'], $x + 430, $this->y+10, 'UTF-8');

            // Vendor Detail
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 30);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $style->setFont($font, 14);
            $page->drawText(__('VENDOR'), $x + 5, $this->y - 18, 'UTF-8');
            $page->drawText(__('SHIP TO'), 300, $this->y - 18, 'UTF-8');

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $style->setFont($font, 12);

            $page->drawText($vendor_name, $x + 5, $this->y - 50, 'UTF-8');
            $page->drawText($vendor_address, $x + 5, $this->y - 70, 'UTF-8');
            $page->drawText('Phone: ' . $vendor_phone, $x + 5, $this->y - 90, 'UTF-8');
            $page->drawText('Email: ' . $vendor_email, $x + 5, $this->y - 110, 'UTF-8');

            $page->drawText($installerobj['name'], 300, $this->y - 50, 'UTF-8');
            $page->drawText($installerobj['address'], 300, $this->y - 70, 'UTF-8');
            $page->drawText($installerobj['city'], 300, $this->y - 90, 'UTF-8');
            $page->drawText('Phone: ' . $installerobj['phone'], 300, $this->y - 110, 'UTF-8');
            $page->drawText('Email: ' . $installerobj['email'], 300, $this->y - 130, 'UTF-8');

            /*                $streetpdf = $shippingAddressHtml2;
                $streetpdf = wordwrap($streetpdf, 50, "&&");
                $streetpdf = explode('&&', $streetpdf);

                $streetbreak = 50;
                foreach ($streetpdf as $key => $value) {
                    $page->drawText($value, 300, $this->y - $streetbreak, 'UTF-8');
                    $streetbreak += 20;
                }
                $streetbreak = $streetbreak;
				$streetbreak = $streetbreak + 20;*/

            $page->drawText(__('Comments: '), $x + 5, $this->y - 170, 'UTF-8');
            $page->drawText($data['comment'], 100, $this->y - 170, 'UTF-8');

            // iTems

            // Vendor Detail
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle(30, $this->y - 200, $page->getWidth() - 30, $this->y - 220);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $style->setFont($font, 14);
            $page->drawText(__('SR#'), $x + 5, $this->y - 215, 'UTF-8');
            $page->drawText(__('DESCRIPTION'), 130, $this->y - 215, 'UTF-8');
            $page->drawText(__('QTY'), 360, $this->y - 215, 'UTF-8');
            $page->drawText(__('UNIT PRICE'), 410, $this->y - 215, 'UTF-8');
            $page->drawText(__('TOTAL'), 500, $this->y - 215, 'UTF-8');

            //ITEM VALUE
            $style->setFont($font, 12);
            $item_y = 245;
            foreach ($data['item'] as $key => $value) {
                $itemno      = $key + 1;
                //$product_obj = $this->productRepository->get($value['sku']);
                try {
                    $product_obj = $this->productRepository->get($value['sku']);
                    // Product exists, you can proceed with your logic here
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // Product doesn't exist, handle the error or take necessary actions
                    // For example, you can log the error, set a default product, or do nothing depending on your requirements
                    $product_obj = null; // or any default value you want
                }
                if ($product_obj == null) {
                    $productName = 'Product not found';
                } else {
                    $productName = $product_obj->getName();
                }
                $rowtotal    = trim($value['price']) * (int) $value['qty'];
                $rowtotal    = number_format($rowtotal, 2);
                $rowtotal    = str_replace(',', '', $rowtotal);

                $page->drawText($itemno, 40, $this->y - $item_y, 'UTF-8');
                $firstString = substr($productName, 0, 41);
                $theRestString = substr($productName, 41);

                $page->drawText($firstString, 80, $this->y - $item_y, 'UTF-8');
                //$productSku = $product_obj->getSku();
                $productSku = $value['sku'];
                if ($theRestString) {
                    $withSku = $theRestString . ' ' . $productSku;
                    $page->drawText($withSku, 80, $this->y - $item_y - 15, 'UTF-8');
                } else {
                    $page->drawText($productSku, 80, $this->y - $item_y - 15, 'UTF-8');
                }
                $page->drawText($value['qty'], 360, $this->y - $item_y, 'UTF-8');
                $page->drawText($this->pricehelper->currency($value['price'], true, false), 410, $this->y - $item_y, 'UTF-8');
                $page->drawText($this->pricehelper->currency($rowtotal, true, false), 490, $this->y - $item_y, 'UTF-8');

                $item_y += 30;
            }

            //subtotal

            /* $page->drawText(__('SUB TOTAL'), 400, $this->y - 330, 'UTF-8');
                $page->drawText($this->pricehelper->currency($data['subtotal'], true, false), 490, $this->y - 330, 'UTF-8');

                $page->drawText(__('VAT 5%'), 400, $this->y - 360, 'UTF-8');
                $page->drawText($this->pricehelper->currency($data['vat'], true, false), 490, $this->y - 360, 'UTF-8');

                $page->drawText(__('GRAND TOTAL'), 400, $this->y - 390, 'UTF-8');
                $page->drawText($this->pricehelper->currency($data['grandtotal'], true, false), 490, $this->y - 390, 'UTF-8'); */
            $page->drawText(__('SUB TOTAL'), 400, $this->y - $item_y, 'UTF-8');
            $page->drawText($this->pricehelper->currency($data['subtotal'], true, false), 490, $this->y - $item_y, 'UTF-8');

            $page->drawText(__('VAT 5%'), 400, $this->y - $item_y - 20, 'UTF-8');
            $page->drawText($this->pricehelper->currency($data['vat'], true, false), 490, $this->y - $item_y - 20, 'UTF-8');

            $page->drawText(__('GRAND TOTAL'), 400, $this->y - $item_y - 40, 'UTF-8');
            $page->drawText($this->pricehelper->currency($data['grandtotal'], true, false), 490, $this->y - $item_y - 40, 'UTF-8');

            // commment and instruction

            $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $style->setFont($font, 12);

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle(30, $this->y - 420, $page->getWidth() - 30, $this->y - 440);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $style->setFont($font, 14);
            $page->drawText(__('SPECIAL INSTRUCTIONS'), 40, $this->y - 435, 'UTF-8');

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle(30, $this->y - 440, $page->getWidth() - 30, $this->y - 510, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $style->setFont($font, 12);

            $page->drawText(__('1. Please mention this order number in your invoices.'), 40, $this->y - 460, 'UTF-8');

            $page->drawText(__('2. Please notify us immediately if you are unable to ship as specified.'), 40, $this->y - 475, 'UTF-8');

            $page->drawText(__('3. The tyre/s to be supplied under this purchase order must comply with UAE law and with standards
				'), 40, $this->y - 490, 'UTF-8');

            $page->drawText(__('approved as per Gulf Technical Regulations by GSO.'), 50, $this->y - 505, 'UTF-8');

            // footer

            $page->drawText(__('If you have any questions about this purchase order, please contact'), 50, $this->y - 540, 'UTF-8');
            $page->drawText(__('[' . $contactPerson . ', ' . $contactPersonPhone . ', Email: ' . $contactPersonEmail . ']'), 50, $this->y - 560, 'UTF-8');

            $fileName = $pdfFile . $data['orderreference_no'] . '.pdf';
            $pdfData  = $pdf->render(); // Get PDF document as a string

            if ($data['submit'] == 'download') {
                $this->fileFactory->create(
                    $fileName,
                    $pdf->render(),
                    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA, // this pdf will be saved in var directory with the name example.pdf
                    'application/octet-stream'
                );
            }

            if ($data['submit'] != 'download') {
                /* Create PDF end*/
                $fileName = $data['orderreference_no'] . '_' . time() . '.pdf';
                $popath   = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'po/' . $fileName;
                file_put_contents($popath, $pdf->render());

                $objectManager     = \Magento\Framework\App\ObjectManager::getInstance();
                $_transportBuilder = $objectManager->create('Hdweb\Purchaseorder\Model\Mail\TransportBuilder');
                $inlineTranslation = $objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
                $storeManager      = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
                $storeManager->setCurrentStore($order->getStore()->getId());
                $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeManager->getStore()->getId());
                $mediaUrl        = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $pdfdownload     = $mediaUrl . 'po/' . $fileName;
                $templateVars    = array(
                    'poreference_no'    => $poreference_no,
                    'bill_to'           => $bill_to,
                    'ordercomment'      => $ordercomment,
                    'order_incrementid' => $order_incrementid,
                    'ship_to'             => $shippingAddressHtml1,
                    'itemtable'         => $itemtable,
                    'podate'            => $podate,
                    'pdfdownload'       => $pdfdownload,
                );
                $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
                $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);

                $copy_to = $this->scopeConfig->getValue('sales_email/order/copy_to', ScopeInterface::SCOPE_STORE);

                $from = array('email' => $email, 'name' => $name);
                $inlineTranslation->suspend();
                //$to = array($installer_detail->getEmail());

                // echo $pdfFile = \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR.'example.pdf';
                // $fileName = "EasyClick-PO-".$order_incrementid.'.pdf';
                // $pdfFile = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().$fileName;

                $emailTemplateId = $this->scopeConfig->getValue('purchaseorder/general/po_email_template_id', ScopeInterface::SCOPE_STORE);
                if ($emailTemplateId != '') {
                    $vendor_copy_email = explode(',', $vendor_copy_email);
                    if ($vendor_data->getEmailCopy()) {
                        $transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($templateVars)
                            ->setFrom($from)
                            ->addTo($vendor_email) // $vendor_email
                            ->addBcc($vendor_copy_email)
                            // ->addAttachment($pdfData)
                            ->getTransport();
                    } else {
                        $transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($templateVars)
                            ->setFrom($from)
                            ->addTo($vendor_email) // $vendor_email
                            ->getTransport();
                    }

                    $transport->sendMessage();
                    $inlineTranslation->resume();
                } else {
                    $this->messageManager->addError(__('Purchase Order Email Template not configured yet!.'));
                }

                /*                            $html= $transport->getMessage()->getBody()->generateMessage();
                $bodyMessage = new \Zend\Mime\Part($html);
                $bodyMessage->type = 'text/html';
                $attachment=$_transportBuilder->addAttachment($pdfData,$fileName);
                $bodyPart = new \Zend\Mime\Message();
                $bodyPart->setParts(array($bodyMessage,$attachment));
                $transport->getMessage()->setBody($bodyPart);
                $transport->sendMessage();
                $inlineTranslation->resume();*/
            }
        }
    }

    public function getpdf()
    {

        $pdf          = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page         = $pdf->pages[0]; // this will get reference to the first page.
        $style        = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $width        = $page->getWidth();
        $hight        = $page->getHeight();
        $x            = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y      = 850 - 150; //print table row from page top – 100px
        //Draw table header row’s
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->drawRectangle(30, $this->y - 20, $page->getWidth() - 30, $this->y + 90, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font, 15);
        $page->setStyle($style);

        $imagePath = 'logo.png';
        $image     = "";
        if ($this->_mediaDirectory->isFile($imagePath)) {
            $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
        }

        $y1 = 800;
        $y2 = 830;
        $x1 = 400;
        $x2 = 530;

        $page->drawImage($image, $x1, $y1, $x2, $y2);

        $page->drawText(__("Tyres Vision"), $x + 5, $this->y + 70, 'UTF-8');
        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("Sharjah Media City (Shams),"), $x + 5, $this->y + 55, 'UTF-8');
        $page->drawText(__("Al Messaned, Al Bataeh, Sharjah, UAE"), $x + 5, $this->y + 40, 'UTF-8');
        $page->drawText(__("Phone:01 234 5678"), $x + 5, $this->y + 25, 'UTF-8');
        $page->drawText(__("Website: www.tyresvision.com "), $x + 5, $this->y + 10, 'UTF-8');

        $page->drawText(__("PURCHASE ORDER"), $x + 350, $this->y + 70, 'UTF-8');
        $page->drawText(__("DATE"), $x + 350, $this->y + 50, 'UTF-8');
        $page->drawText(__("PO/ORDER #"), $x + 350, $this->y + 30, 'UTF-8');
        $page->drawText(__("TRN #"), $x + 350, $this->y + 10, 'UTF-8');

        //Po value
        $page->drawText(__("6/9/12"), $x + 430, $this->y + 50, 'UTF-8');
        $page->drawText($data['poreference_no'], $x + 430, $this->y + 30, 'UTF-8');
        $page->drawText($data['orderreference_no'], $x + 430, $this->y + 10, 'UTF-8');

        // Vendor Detail
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 30);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 14);
        $page->drawText(__('VENDOR'), 40, $this->y - 18, 'UTF-8');
        $page->drawText(__('SHIP TO:'), 300, $this->y - 18, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 12);

        $page->drawText(__('Devvedra patel'), 40, $this->y - 50, 'UTF-8');
        $page->drawText(__('T:34343434243'), 40, $this->y - 70, 'UTF-8');
        $page->drawText(__('Email:test@gmail.com'), 40, $this->y - 90, 'UTF-8');

        $page->drawText(__('Devvedra patel'), 300, $this->y - 50, 'UTF-8');
        $page->drawText(__('Installer Address'), 300, $this->y - 70, 'UTF-8');
        $page->drawText(__('Installer Contact Person'), 300, $this->y - 90, 'UTF-8');
        $page->drawText(__('T:34343434243'), 300, $this->y - 110, 'UTF-8');

        $page->drawText(__('Comment:'), 40, $this->y - 170, 'UTF-8');
        $page->drawText(__('Test comment'), 100, $this->y - 170, 'UTF-8');

        // iTems

        // Vendor Detail
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y - 200, $page->getWidth() - 30, $this->y - 220);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 14);
        $page->drawText(__('ITEM #'), 40, $this->y - 215, 'UTF-8');
        $page->drawText(__('DESCRIPTION'), 130, $this->y - 215, 'UTF-8');
        $page->drawText(__('QTY'), 320, $this->y - 215, 'UTF-8');
        $page->drawText(__('UNIT PRICE'), 370, $this->y - 215, 'UTF-8');
        $page->drawText(__('TOTAL'), 500, $this->y - 215, 'UTF-8');

        //ITEM VALUE
        $style->setFont($font, 12);
        $page->drawText(__('1'), 40, $this->y - 245, 'UTF-8');
        $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 245, 'UTF-8');
        $page->drawText(__('2'), 330, $this->y - 245, 'UTF-8');
        $page->drawText(__('AED 1200'), 370, $this->y - 245, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 245, 'UTF-8');

        $page->drawText(__('2'), 40, $this->y - 270, 'UTF-8');
        $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 270, 'UTF-8');
        $page->drawText(__('2'), 330, $this->y - 270, 'UTF-8');
        $page->drawText(__('AED 1200'), 370, $this->y - 270, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 270, 'UTF-8');

        $page->drawText(__('3'), 40, $this->y - 300, 'UTF-8');
        $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 300, 'UTF-8');
        $page->drawText(__('2'), 330, $this->y - 300, 'UTF-8');
        $page->drawText(__('AED 1200'), 370, $this->y - 300, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 300, 'UTF-8');

        //subtotal

        $page->drawText(__('SUBTOTAL'), 400, $this->y - 330, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 330, 'UTF-8');

        $page->drawText(__('TAX'), 400, $this->y - 360, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 360, 'UTF-8');

        $page->drawText(__('GRAND TOTAL'), 400, $this->y - 390, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 390, 'UTF-8');

        // commment and instruction

        $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 12);

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y - 420, $page->getWidth() - 30, $this->y - 440);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 14);
        $page->drawText(__('COMMMENT OR SPECIAL INSTRUCTIONS'), 40, $this->y - 435, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y - 440, $page->getWidth() - 30, $this->y - 510, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 12);

        $page->drawText(__('1. Please mention this Order number in your invoices for this order'), 40, $this->y - 460, 'UTF-8');

        $page->drawText(__('2. Please notify us immediately if you are unable to ship as specified'), 40, $this->y - 475, 'UTF-8');

        $page->drawText(__('3. The tyre/s to be supplied under this purchase order must comply with UAE law and with standards
            '), 40, $this->y - 490, 'UTF-8');

        $page->drawText(__('approved as Gulf Technical Regulations by GSO'), 50, $this->y - 505, 'UTF-8');

        // footer

        $page->drawText(__('If you have any questions about this purchase order, please contact'), 150, $this->y - 540, 'UTF-8');
        $page->drawText(__('[Mr. Devendra, 0543473401 or Email: devendra.it@live.com]'), 130, $this->y - 560, 'UTF-8');

        $fileName = 'example.pdf';

        $this->fileFactory->create(
            $fileName,
            $pdf->render(),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR, // this pdf will be saved in var directory with the name example.pdf
            'application/pdf'
        );
    }
}
