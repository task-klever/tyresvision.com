<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

class AmpReplaceImageProcessor extends LazyLoadProcessor
{
    const IMAGE_REGEXP = '<amp-img([^>]*?)src=(\"|\'|)(.*?)(\"|\'| )(.*?)>';

    public function processImages(&$output)
    {
        if ($this->getLazyConfig()->getData(self::IS_REPLACE_WITH_USER_AGENT)) {
            $this->replaceImages($output);
        }
    }
}
