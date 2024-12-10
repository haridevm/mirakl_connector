<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\Image;

use Magento\Framework\Exception\LocalizedException;

class Converter
{
    /**
     * @param string $file
     * @throws LocalizedException
     */
    public function convertWebpToJpeg($file)
    {
        $img = imagecreatefromwebp($file);

        if (!$img || !imagejpeg($img, $file)) {
            throw new LocalizedException(__('Could not convert webp image to jpeg'));
        }

        imagedestroy($img);
    }
}
