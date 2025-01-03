<?php
/**
 * Webcastle_CategoryImportExport
 *
 * @category   Webcastle
 * @package    Webcastle_CategoryImportExport
 * @author     Anjaly K V - Webcastle Media
 * @copyright  2023
 */
namespace Webcastle\CategoryImportExport\Controller\Adminhtml\CategoryImport;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    protected $registry;
    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;
    protected $_fileUploaderFactory;
    protected $_filesystem;
    protected $_moduleReader;
    protected $_fileCsv;
    protected $_storeManager;
    protected $_categoryFactory;
    protected $_logger;
    protected $_fileio;

    /**
     * constructor
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\File\Csv $fileCsv,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem\Io\File $fileio,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->registry = $registry;
        $this->_backendSession = $context->getSession();
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_filesystem = $fileSystem;
        $this->_moduleReader = $moduleReader;
        $this->_fileCsv = $fileCsv;
        $this->_storeManager = $storeManagerInterface;
        $this->_categoryFactory = $categoryFactory;
        $this->_logger = $logger;
        $this->_fileio = $fileio;
        parent::__construct($context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->registry->register('isSecureArea', true);
        try {
            $filepath = $this->_uploadFileAndGetName();
            if ($filepath!='' && file_exists($filepath)) {
                chmod($filepath, 0777);
                $data = $this->_fileCsv->getData($filepath);
                if (isset($data[0]) && !empty($data[0])) {
                    $header = $data[0];
                    $categorieskey = array_search('categories', $header);
                    $categoryidkey = array_search('category_id', $header);
                    $storedata = array_search('store', $header);

                    $websiteId = $this->_storeManager->getWebsite()->getWebsiteId();
                    $store = $this->_storeManager->getStore();
                    $storeId = $store->getStoreId();
                    $singlestoremode = $this->_storeManager->isSingleStoreMode();
                    $_stores = [];
                    if (!$singlestoremode) {
                        $stores = $this->_storeManager->getStores();
                        foreach ($stores as $key => $store) {
                            $_stores[$store->getCode()] = $store->getId();
                        }
                    }
                    $rootNodeId = $store->getRootCategoryId();
                    $rootCat = $this->_categoryFactory->create();
                    $cat_info = $rootCat->load($rootNodeId);

                        $alreadyexist = [];
                        $categorycollection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect('name');
                        $exist_categories_name = [];
                        $exist_categories_path = [];
                        $exist_categories_pathname = [];
                    foreach ($categorycollection as $key => $value) {
                        $exist_categories_name[$value->getId()] = $value->getName();
                        $exist_categories_path[$value->getId()] = $value->getPath();
                        $checkcat = $this->_categoryFactory->create();
                        $categoryobj = $checkcat->load($value->getId());
                        $parentcatnames = [];
                        $parentid = '';
                        foreach ($this->getparentCategories($categoryobj) as $key => $parentcate) {
                            $parentcatnames[] = $parentcate->getName();
                            $parentid = $parentcate->getId();
                        }
                        $parent_cat = implode('/', $parentcatnames);
                        if ($parent_cat && $parentid) {
                            $exist_categories_pathname[$parentid] = $parent_cat;
                        }
                    }
                    foreach ($data as $key => $categoryitem) {
                        if ($key!=0) {
                            $cat_data = $this->_getKeyValue($categoryitem, $header);
                            if (isset($cat_data['category_id'])) {
                                unset($cat_data['category_id']);
                            }
                            if (isset($categorieskey) && ($categorieskey!='' || $categorieskey===0)) {
                                $array_key = array_search($categoryitem[$categorieskey], $exist_categories_pathname);
                                if ($array_key) {
                                    $alreadyexist[] = $categoryitem[$categorieskey];
                                } else {
                                    $strmark = strrpos($categoryitem[$categorieskey], '/');
                                    $_parentid = '';
                                    $newcategory = '';
                                    if ($strmark!=false) {
                                        $parentpath = substr($categoryitem[$categorieskey], 0, ($strmark));
                                        $newcategory = substr($categoryitem[$categorieskey], ($strmark)+1);
                                        /* start extra added */
                                        $newcategory = str_replace('_', '/', $newcategory);
                                        /* end extra added */
                                        $_parentid = array_search($parentpath, $exist_categories_pathname);
                                    } else {
                                        $newcategory = $categoryitem[$categorieskey];
                                        $_parentid = $cat_info->getId();
                                    }
                                    if ($_parentid!='' && $newcategory!='') {
                                        $cateitem = $this->_categoryFactory->create();
                                        $cateitem->setData($cat_data);
                                        $parentcategory = $this->_categoryFactory->create();
                                        $parentcategory->load($_parentid);
                                        if ($parentcategory->getId()) {
                                            $cateitem->setParentId($_parentid);
                                            $cateitem->setPath($parentcategory->getPath());
                                        }
                                        $cateitem->setAttributeSetId($cateitem->getDefaultAttributeSetId());
                                        $cateitem->setName($newcategory);
                                        $_url_key = str_replace(' ', '-', strtolower($newcategory));
                                        /* start extra added */
                                        $_url_key = str_replace('/', '-', strtolower($newcategory));
                                        /* end extra added */
                                        if (in_array($newcategory, $exist_categories_name)) {
                                            $_url_key .= '-'.random_int(10, 99);
                                        }
                                        $cateitem->setUrlKey($_url_key);
                                        $cateitem->setStoreId($storeId);
                                        $cateitem->save();
                                        if ($cateitem->getId()) {
                                            $exist_categories_name[$cateitem->getId()] = $cateitem->getName();
                                            $exist_categories_path[$cateitem->getId()] = $cateitem->getPath();
                                            $exist_categories_pathname[$cateitem->getId()] = $categoryitem[$categorieskey];
                                        }
                                    }
                                }
                            } elseif (isset($categoryidkey) && ($categoryidkey!='' || $categoryidkey===0) && $storedata!='') {
                                //update categories
                                $catemodel = $this->_categoryFactory->create();
                                if (!$singlestoremode && isset($_stores[$categoryitem[$storedata]])) {
                                        $catemodel->setStoreId($_stores[$categoryitem[$storedata]]);
                                } else {
                                    $catemodel->setStoreId($storeId);
                                }
                                $cateitem = $catemodel->load($categoryitem[$categoryidkey]);
                                $nocategoryfound = true;
                                if ($cateitem->getId()) {
                                    $nocategoryfound = false;
                                    $attributesetid = $cateitem->getAttributeSetId();
                                    $_parentid = $cateitem->getParentId();
                                    foreach ($cat_data as $key => $value) {
                                        /* in below array extra added "store" */
                                        if (!in_array($key, ['url_key','category_id','url_path','path','level','children_count','full_path','store'])) {
                                            $cateitem->setData($key, $value);
                                        }
                                    }
                                    $parentid = $cateitem->getParentId();
                                    if ($parentid!=$_parentid && $cateitem->getId()>2) {
                                        $_catemodel = $this->_categoryFactory->create();
                                        $parentcat = $_catemodel->load($parentid);
                                        if ($parentcat->getId()) {
                                            $cateitem->setPath($parentcat->getPath().'/'.$cateitem->getId());
                                        } else {
                                            $this->messageManager->addError('Parent category not Found.');
                                            $resultRedirect->setPath('webcastle_categoryimportexport/*/edit');
                                            return $resultRedirect;
                                        }
                                        $cateitem->move($parentid, false);
                                    }
                                    if ($cateitem->getId()<=2) {
                                        $cateitem->unsetData('posted_products');
                                    }
                                    $cateitem->setStoreId(0); //added extra this line
                                    $cateitem->save();
                                }
                            } else {
                                $this->messageManager->addError('Data Column not Found.');
                                $resultRedirect->setPath('webcastle_categoryimportexport/*/edit');
                                return $resultRedirect;
                            }
                        }
                    }
                    if (isset($alreadyexist) && !empty($alreadyexist)) {
                        $this->messageManager->addError(__('This categories are already exist: ').implode(', ', $alreadyexist));
                        $this->messageManager->addSuccess(__('Other categories has been imported Successfully'));
                    } elseif (isset($categoryidkey) && $categoryidkey===0) {
                        if ($nocategoryfound) {
                            $this->messageManager->addError(__('No Category Found.'));
                        } else {
                            $this->messageManager->addSuccess(__('Categories has been updated Successfully'));
                        }
                    } else {
                        $this->messageManager->addSuccess(__('Categories has been imported Successfully'));
                    }
                        unlink($filepath);
                        $this->_backendSession->setNavinImportcategoryTestData(false);
                        $resultRedirect->setPath('webcastle_categoryimportexport/*/edit');
                        return $resultRedirect;
                } else {
                    $this->messageManager->addError('Data Not Found.');
                    $resultRedirect->setPath('webcastle_categoryimportexport/*/edit');
                    return $resultRedirect;
                }
            } else {
                $this->messageManager->addError('File not Found.');
                $resultRedirect->setPath('webcastle_categoryimportexport/*/edit');
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->debug($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->_logger->debug($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            $this->messageManager->addException($e, __('Something went wrong while saving the category.'));
        }
        $resultRedirect->setPath(
            'webcastle_categoryimportexport/*/edit',
            [
                '_current' => true
            ]
        );
        return $resultRedirect;
    }

    protected function _uploadFileAndGetNameBKP()
    {
        $uploader = $this->_fileUploaderFactory->create(['fileId' => 'file']);
        $uploader->setAllowedExtensions(['CSV', 'csv']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
        ->getAbsolutePath('categoryimport');

        if (!is_dir($path)) {
            $this->_fileio->mkdir($path, '0777', true);
            $this->_fileio->chmod($path, '0777', true);
        }
        $result = $uploader->save($path.'/');
        if (isset($result['file']) && !empty($result['file'])) {
            return $result['path'].$result['file'];
        }
        return false;
    }

    protected function _uploadFileAndGetName()
    {
        /*_uploadFileAndGetNameBKP is above which is og old function */
        $uploader = $this->_fileUploaderFactory->create(['fileId' => 'file']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'webp', 'csv', 'CSV']);
        $uploader->setAllowRenameFiles(true);
        if (!$uploader->checkMimeType([])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('File validation failed.'));
        }
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $destinationPath = $mediaDirectory->getAbsolutePath('webcastle/categoryimport');
        $result = $uploader->save($destinationPath);
        if (isset($result['file']) && !empty($result['file'])) {
            return $result['path'].'/'.$result['file'];
        }
        return false;
    }

    protected function _getKeyValue($row, $headerArray)
    {
        $temp = [];
        foreach ($headerArray as $key => $value) {
            if ($value=='image') {
                $temp[$value] = $this->_getImagePath($row[$key]);
            } elseif ($value=='products' && $row[$key]!='') {
                $temp['posted_products'] = array_flip(explode('|', $row[$key]));
            } else {
                $temp[$value] = $row[$key];
            }
        }
        return $temp;
    }

    protected function _getImagePath($categoryimage)
    {
        $weburl = strpos($categoryimage, 'http://');
        if ($weburl!==false) {
            $imagepath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('catalog/category');
            $this->_fileio->mkdir($imagepath, '0777', true);
            $file = file_get_contents($categoryimage);
            if ($file!='') {
                $allowed =  ['gif','png' ,'jpg', 'jpeg'];
                $ext = strtolower(pathinfo($categoryimage, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $imagename = pathinfo($categoryimage, PATHINFO_BASENAME);
                    if (!is_dir($imagepath)) {
                        $this->_fileio->mkdir($imagepath, '0777', true);
                        $this->_fileio->chmod($imagepath, '0777', true);
                    }
                    $imagepath = $imagepath.'/'.$imagename;
                    $result = file_put_contents($imagepath, $file);
                    if ($result) {
                        return $imagename;
                    }
                }
            }
        } else {
            return $categoryimage;
        }
    }

    protected function getparentCategories($category)
    {
        $pathIds = array_reverse(explode(',', $category->getPathInStore()));
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories = $this->_categoryFactory->create()->getCollection();
        return $categories->setStore(
            $this->_storeManager->getStore()
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'url_key'
        )->addFieldToFilter(
            'entity_id',
            ['in' => $pathIds]
        )->load()->getItems();
    }
}
