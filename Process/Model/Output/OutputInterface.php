<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Output;

interface OutputInterface
{
    /**
     * @return $this
     */
    public function close(): self;

    /**
     * @param string $str
     * @return $this
     */
    public function display(string $str): self;

    /**
     * @return string
     */
    public function getType(): string;
}
