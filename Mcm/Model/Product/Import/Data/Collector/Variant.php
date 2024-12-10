<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Collector;

use Mirakl\Mci\Helper\Data as MciHelper;

class Variant implements CollectorInterface
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
    public function collect(array $data): array
    {
        $variants = array_intersect_key($data, $this->mciHelper->getVariantAttributes());

        return array_filter($variants);
    }
}
