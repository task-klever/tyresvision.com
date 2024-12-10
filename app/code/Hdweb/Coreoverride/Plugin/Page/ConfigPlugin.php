<?php
namespace Hdweb\Coreoverride\Plugin\Page;

use Magento\Cms\Model\Page as CmsPageModel;

class ConfigPlugin
{
    /**
     * @var CmsPageModel
     */
    private $cmsPageModel;

    /**
     * ConfigPlugin constructor.
     * @param CmsPageHelper $cmsPageModel
     */
    public function __construct(
        CmsPageModel $cmsPageModel
    ) {
        $this->cmsPageModel = $cmsPageModel;
    }
    /**
     * @param \Magento\Framework\View\Page\Config $subject
     * @param string $className
     * @return array
     */
    public function beforeAddBodyClass(\Magento\Framework\View\Page\Config $subject, $className)
    {
        $cmsPageIdentifier = 'b2b.html';
        
        if ($this->cmsPageModel->getIdentifier() === $cmsPageIdentifier) {
            // Remove the unwanted class
            $unwantedClass = 'cms-page-view';
            $className = preg_replace('#[^a-z0-9-_]+#', '-', strtolower($className));
            $bodyClasses = $subject->getElementAttribute(\Magento\Framework\View\Page\Config::ELEMENT_TYPE_BODY, \Magento\Framework\View\Page\Config::BODY_ATTRIBUTE_CLASS);
            $bodyClasses = $bodyClasses ? explode(' ', $bodyClasses) : [];
            $bodyClasses = array_diff($bodyClasses, [$unwantedClass]);
            $subject->setElementAttribute(
                \Magento\Framework\View\Page\Config::ELEMENT_TYPE_BODY,
                \Magento\Framework\View\Page\Config::BODY_ATTRIBUTE_CLASS,
                implode(' ', $bodyClasses)
            );
        }

        return [$className];
    }
}
