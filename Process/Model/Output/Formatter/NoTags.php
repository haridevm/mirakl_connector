<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Output\Formatter;

class NoTags implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function format(string $str): string
    {
        return strip_tags($str);
    }
}
