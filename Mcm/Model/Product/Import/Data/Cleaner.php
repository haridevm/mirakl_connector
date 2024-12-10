<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data;

use Mirakl\Mcm\Model\Product\Import\Data\Cleaner\CleanerInterface;

class Cleaner
{
    /**
     * @var CleanerInterface[]
     */
    private $cleaners;

    /**
     * @param CleanerInterface[] $cleaners
     */
    public function __construct(array $cleaners = [])
    {
        $this->cleaners = $cleaners;
    }

    /**
     * @param array $data
     */
    public function clean(array &$data): void
    {
        foreach ($this->cleaners as $cleaner) {
            $cleaner->clean($data);
        }
    }
}