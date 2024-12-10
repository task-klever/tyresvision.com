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

class Edit extends \Magento\Backend\App\Action
{
    protected $_backendSession;
    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    protected $_filesystem;
    protected $_fileio;
    

    /**
     * constructor
     *
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Io\File $fileio,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->_backendSession    = $context->getSession();
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_filesystem = $fileSystem;
        $this->_fileio = $fileio;
        parent::__construct($context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webcastle_CategoryImportExport::categoryimportexport');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $imagepath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('catalog');
        $this->_fileio->mkdir($imagepath, '0777', true);
        $imagepath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('catalog/category');
        $this->_fileio->mkdir($imagepath, '0777', true);
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
        ->getAbsolutePath('categoryimport');
        $this->_fileio->mkdir($path, '0777', true);
        if (!is_writable($imagepath)) {
            $this->messageManager->addNotice(__('Please make this directory path writable pub/media/catalog/category'));
        }
        if (!is_writable($path)) {
            //$this->messageManager->addNotice(__('Please make this directory path writable var/categoryimport'));
            //$this->messageManager->addNotice(__('Please make sure this directory path is writable pub/media/webcastle/categoryimport'));
            //$this->messageManager->addNotice(__('If your category name is for example "195/65 R15" then in the csv write name like this "195_65 R15", It will create category as you want with name "195/65 R15"'));
        }
        $this->messageManager->addNotice(__('Please make sure this directory path is writable pub/media/webcastle/categoryimport'));
        $this->messageManager->addNotice(__('If your category name is for example "195/65 R15" then in the csv write name like this "195_65 R15", It will create category as you want with name "195/65 R15"'));
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webcastle_CategoryImportExport::categoryimportexport');
        $resultPage->getConfig()->getTitle()->prepend('Import Categories');
        return $resultPage;
    }
}
