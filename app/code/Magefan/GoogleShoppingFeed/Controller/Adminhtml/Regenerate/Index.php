<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\GoogleShoppingFeed\Controller\Adminhtml\Regenerate;

use Magefan\GoogleShoppingFeed\Model\Config;
use Magefan\GoogleShoppingFeed\Model\XmlFeed;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\Controller\ResultFactory;
use Magefan\GoogleShoppingFeed\Cron\RefreshDataAsync;
use Magento\Framework\Filesystem\Io\File;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Magefan_GoogleShoppingFeed::mfgoogleshoppinfeed';

    /**
     * @var XmlFeed
     */
    private $xmlFeed;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var File
     */
    private  $file;

    /**
     * @var Filesystem|mixed
     */
    private $filesystem;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param XmlFeed $xmlFeed
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param File $file
     * @param Filesystem|null $filesystem
     */
    public function __construct(
        Context          $context,
        PageFactory      $resultPageFactory,
        XmlFeed          $xmlFeed,
        Config           $config,
        ManagerInterface $messageManager,
        File       $file,
        Filesystem $filesystem = null
    ) {
        $this->xmlFeed = $xmlFeed;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->file = $file;
        parent::__construct($context);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->filesystem = $filesystem ?: $objectManager->create(Filesystem::class);
    }

    /**
     * @return Redirect|ResultInterface
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            try {
                if ($this->getRequest()->getParam('async')){
                    $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
                    $rootPath = $varDirectory->getAbsolutePath();
                    $this->file->write($rootPath . RefreshDataAsync::GOOGLE_FEED_RUNNING_FLAG_FILE, ' ');
                    $this->messageManager->addSuccessMessage('Google feed generation has been scheduled successfully.');
                }else {
                    $this->xmlFeed->generate();
                    $this->messageManager->addSuccessMessage('Google feed updated successfully.');
                }

                
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('Error while updating Google feed. ' . $e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage('To generate Google Shopping Feed, please enable the extension first.');
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
