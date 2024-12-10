<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Output\Formatter;

interface FormatterInterface
{
    /**
     * @param string $str
     * @return string
     */
    public function format(string $str): string;
}
