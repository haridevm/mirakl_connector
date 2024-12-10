<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

use Mirakl\Mci\Helper\Data as MciHelper;

class Images implements FormatterInterface
{
    /**
     * @var MciHelper
     */
    private $mciHelper;

    /**
     * @param MciHelper $mciHelper
     */
    public function __construct(MciHelper $mciHelper)
    {
        $this->mciHelper = $mciHelper;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data): void
    {
        if (empty($data['images'])) {
            return;
        }

        $imagesAttributes = $this->mciHelper->getImagesAttributes();
        $i = 0;
        foreach (array_keys($imagesAttributes) as $code) {
            if (isset($data['images'][$i])) {
                $data[$code] = $data['images'][$i];
            } else {
                break;
            }
            $i++;
        }
    }
}
