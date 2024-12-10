<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleShoppingFeed\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Exclude extends AbstractSource
{
    const MF_GOOGLE_USE_PARENT = 0;
    const MF_GOOGLE_EXCLUDE_NO = 1;
    const MF_GOOGLE_EXCLUDE_YES = 2;

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions(): array
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => self::MF_GOOGLE_USE_PARENT, 'label' => 'Use parent category settings'],
                ['value' => self::MF_GOOGLE_EXCLUDE_NO, 'label' => 'No'],
                ['value' => self::MF_GOOGLE_EXCLUDE_YES, 'label' => 'Yes']
            ];
        }

        return $this->_options;
    }
}
