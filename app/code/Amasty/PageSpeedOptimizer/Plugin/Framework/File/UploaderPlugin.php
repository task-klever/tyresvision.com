<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


declare(strict_types=1);

namespace Amasty\PageSpeedOptimizer\Plugin\Framework\File;

use Amasty\PageSpeedOptimizer\Plugin\Image\AbstractImage;
use Magento\Framework\File\Uploader;

class UploaderPlugin extends AbstractImage
{
    /**
     * @param Uploader $subject
     * @param bool|array $result
     * @return mixed
     */
    public function afterSave(Uploader $subject, $result)
    {
        if (!isset($result['path'])) {
            return $result;
        }

        if ($this->isAutoOptimizationAllowed() && $this->isImageMimeTypeAllowed($result['type'] ?? '')) {
            $imageFilePath = $result['path'] . DIRECTORY_SEPARATOR . $result['file'];

            if ($image = $this->prepareFile($imageFilePath)) {
                $this->imageProcessor->execute($image);
            }
        }

        return $result;
    }

    private function isImageMimeTypeAllowed(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES);
    }
}
