<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\GoogleShoppingFeed\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const MF_ENABLE_PATH = 'mfgoogleshoppinfeed/general/enabled';
    const MF_FEED_TITLE = 'mfgoogleshoppinfeed/general/feed_title';
    const MF_FEED_DESCRIPTION = 'mfgoogleshoppinfeed/general/feed_description';
    const MF_FEED_SETTINGS = 'mfgoogleshoppinfeed/attributes/mapping';
    const MF_PRODUCT_FILTER_SETTINGS = 'mfgoogleshoppinfeed/conditions/';
    const MF_GOOGLE_ANALYTICS_PATH = 'mfgoogleshoppinfeed/google_analytics/';
    const MF_FEED_CONFIGURABLE_PRODUCT_TYPE = 'mfgoogleshoppinfeed/product_types/configurable/include_configurable';
    const MF_FEED_CONFIGURABLE_INCLUDE_CHILD = 'mfgoogleshoppinfeed/product_types/configurable/include_child';
    const MF_FEED_GENERATION_CURRENCY_TYPE = 'mfgoogleshoppinfeed/generate_feed/multi_currency';
    const MF_FEED_FOLDER_NAME = 'mfgoogleshoopingfeed';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $attributesMapping;

    /**
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $configPath
     * @return mixed
     */
    protected function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getConfig(
            self::MF_ENABLE_PATH
        );
    }

    /**
     * @param $element
     * @return string
     */
    public function getGoogleAnalytics($element): string
    {
        return (string)$this->getConfig(
            self::MF_GOOGLE_ANALYTICS_PATH . $element
        );
    }

    /**
     * @return string
     */
    public function getFeedTitle(): string
    {
        return (string)$this->getConfig(
            self::MF_FEED_TITLE
        );
    }

    /**
     * @return string
     */
    public function getFeedDescription(): string
    {
        return (string)$this->getConfig(
            self::MF_FEED_DESCRIPTION
        );
    }

    /**
     * @param $field
     * @return string
     */
    public function getProductFilterSetting($field): string
    {
        return (string)$this->getConfig(
            self::MF_PRODUCT_FILTER_SETTINGS . $field
        );
    }

    /**
     * @return array
     */
    public function getAttributesMapping(): array
    {
        if (null === $this->attributesMapping) {
            $data = $this->getConfig(
                self::MF_FEED_SETTINGS
            );

            $this->attributesMapping = [];
            if ($data) {
                $values = $this->serializer->unserialize($data);
                foreach ($values as $item) {
                    $this->attributesMapping[$item['tag']] = $item;
                }
            }
        }

        return $this->attributesMapping;
    }

    /**
     * @return string
     */
    public function isIncludeConfigurableProducts(): string
    {
        return (bool)$this->getConfig(
            self::MF_FEED_CONFIGURABLE_PRODUCT_TYPE
        );
    }

    /**
     * @return string
     */
    public function isIncludeChildProducts(): string
    {
        return (bool)$this->getConfig(
            self::MF_FEED_CONFIGURABLE_INCLUDE_CHILD
        );
    }

    /**
     * @return int
     */
    public function generationCurrencyType(): int
    {
        return (int)$this->getConfig(
            self::MF_FEED_GENERATION_CURRENCY_TYPE
        );
    }
}
