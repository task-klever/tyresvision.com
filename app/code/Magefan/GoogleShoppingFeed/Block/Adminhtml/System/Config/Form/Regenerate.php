<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Block\Adminhtml\System\Config\Form;

use Magefan\GoogleShoppingFeed\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Regenerate extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $_template = 'Magefan_GoogleShoppingFeed::system/config/button.phtml';

    /**
     * @var Config
     */
    private $config;

    protected $scopeConfig;

    /**
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config  $config,
        ScopeConfigInterface $scopeConfig,
        array   $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }


    /**
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'mf-google-shopping-feed',
                'label' => __('Regenerate'),
                'onclick' => 'window.location ="' . $this->getUrl('mf_google_feed/regenerate/index') . '";'
            ]
        );
        return $button->toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAsyncButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'mf-google-shopping-feed-async',
                'label' => __('Regenerate Asynchronously'),
                'onclick' => 'window.location ="' . $this->getUrl('mf_google_feed/regenerate/index') . '?async=1' . '";'
            ]
        );
        return $button->toHtml();
    }

    /**
     * @return array
     * @throws FileSystemException
     */
    public function getFeedByStore(): array
    {
        $feedList = [];
        $mediaDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $feedDirectory = $mediaDirectory->isDirectory(Config::MF_FEED_FOLDER_NAME);

        if (!$feedDirectory) {
            return $feedList;
        }

        foreach ($this->_storeManager->getStores() as $store) {
            $currencyCodes = [];
            $currencyCodes[] = $store->getDefaultCurrency()->getCurrencyCode();

            if ($this->config->generationCurrencyType()){
                $currencyCodes = $store->getAvailableCurrencyCodes(true);
            }

            foreach ($currencyCodes as $currencyCode) {
                $codeLower = strtolower($currencyCode);
                $filePath = $mediaDirectory->getAbsolutePath() . Config::MF_FEED_FOLDER_NAME . '/'. $store->getCode() . '_' . $codeLower . '.xml';

                if ($store->getIsActive() && $this->directory->isExist($filePath)) {
                    $websiteLink = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                    $fileLink = $websiteLink . Config::MF_FEED_FOLDER_NAME . '/'. $store->getCode() . '_' . $codeLower . '.xml';

                    $feedList[] = ['name' => $store->getCode() . '_' . strtolower($currencyCode), 'link' => $fileLink];
                }

                //$categoriesToGenerate = ['Tyres', 'Car Batteries', 'Auto Parts', 'Rim Protectors']; // Add your category names
                $categoriesToGenerateConfigVal = $this->scopeConfig->getValue('mfgoogleshoppinfeed/general/feed_for_categories', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $categoriesToGenerate = explode(',', $categoriesToGenerateConfigVal);
                foreach ($categoriesToGenerate as $categoryName) {
                    $categoryNameForFile = str_replace(' ', '_', $categoryName);
                    $filePath = $mediaDirectory->getAbsolutePath() . Config::MF_FEED_FOLDER_NAME . '/'. $store->getCode() . '_' . $codeLower . '_' . strtolower($categoryNameForFile) . '.xml';

                    if ($store->getIsActive() && $this->directory->isExist($filePath)) {
                        $websiteLink = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                        $fileLink = $websiteLink . Config::MF_FEED_FOLDER_NAME . '/'. $store->getCode() . '_' . $codeLower . '_' . strtolower($categoryNameForFile) . '.xml';
    
                        $feedList[] = ['name' => strtolower($categoryNameForFile), 'link' => $fileLink];
                    }
                }
            }
        }

        return $feedList;
    }
}
