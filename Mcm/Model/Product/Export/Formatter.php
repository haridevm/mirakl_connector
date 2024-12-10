<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export;

use Mirakl\Mcm\Model\Product\Export\Formatter\FormatterInterface;

class Formatter
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

    /**
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters = [])
    {
        $this->formatters = $formatters;
    }

    /**
     * @param array $data
     * @return void
     */
    public function format(array &$data): void
    {
        foreach ($this->formatters as $formatter) {
            $formatter->format($data);
        }
    }
}
