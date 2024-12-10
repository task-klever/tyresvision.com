<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Cron;

use Magefan\GoogleShoppingFeed\Model\Config;
use Magefan\GoogleShoppingFeed\Model\XmlFeed;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class RefreshDataAsync
{

    const GOOGLE_FEED_RUNNING_FLAG_FILE = 'var/google-feed-async.flag';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var XmlFeed
     */
    private $xmlFeed;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Filesystem|mixed
     */
    private $filesystem;

    /**
     * @param XmlFeed $xmlFeed
     * @param Config $config
     * @param File $file
     * @param Filesystem|null $filesystem
     */
    public function __construct(
        XmlFeed    $xmlFeed,
        Config     $config,
        File       $file,
        Filesystem $filesystem = null
    )
    {
        $this->xmlFeed = $xmlFeed;
        $this->config = $config;
        $this->file = $file;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->filesystem = $filesystem ?: $objectManager->create(Filesystem::class);
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if ($this->config->isEnabled()) {
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
            $rootPath = $directory->getAbsolutePath();

            if ($this->file->fileExists($rootPath . self::GOOGLE_FEED_RUNNING_FLAG_FILE)) {
                $this->file->rm($rootPath . self::GOOGLE_FEED_RUNNING_FLAG_FILE);
                $this->xmlFeed->generate();
            }
        }
    }
}
