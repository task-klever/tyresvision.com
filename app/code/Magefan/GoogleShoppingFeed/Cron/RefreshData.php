<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Cron;

use Magefan\GoogleShoppingFeed\Model\Config;
use Magefan\GoogleShoppingFeed\Model\XmlFeed;

class RefreshData
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var XmlFeed
     */
    private $xmlFeed;

    /**
     * @param XmlFeed $xmlFeed
     * @param Config $config
     */
    public function __construct(
        XmlFeed $xmlFeed,
        Config  $config
    ) {
        $this->xmlFeed = $xmlFeed;
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->config->isEnabled()) {
            $this->xmlFeed->generate();
        }
    }
}
