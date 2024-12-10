<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Cron;

use Magefan\GoogleShoppingFeed\Model\Config;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class RemoveInvalidFeed
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $file;

    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param File $file
     */
    public function __construct(
        Config                $config,
        StoreManagerInterface $storeManager,
        Filesystem            $filesystem,
        File $file
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    public function execute(): void
    {
        if ($this->config->isEnabled()) {
            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $feedDirectory = $mediaDirectory->isDirectory(Config::MF_FEED_FOLDER_NAME);

            if (!$feedDirectory) {
                return;
            }

            foreach ($this->storeManager->getStores() as $store) {
                $validCurrencyCodes = [];
                $validCurrencyCodes[] = $store->getDefaultCurrency()->getCurrencyCode();

                if ($this->config->generationCurrencyType()) {
                    $validCurrencyCodes = $store->getAvailableCurrencyCodes(true);
                }

                foreach ($store->getAvailableCurrencyCodes(true) as $currencyCode) {
                    $codeLower = strtolower($currencyCode);
                    $filePath = $mediaDirectory->getAbsolutePath() . Config::MF_FEED_FOLDER_NAME . '/' . $store->getCode() . '_' . $codeLower . '.xml';

                    if ($store->getIsActive() && $this->file->isExists($filePath) && !in_array($currencyCode, $validCurrencyCodes)) {
                        $this->file->deleteFile($filePath);
                    }
                }
            }
        }
    }
}
