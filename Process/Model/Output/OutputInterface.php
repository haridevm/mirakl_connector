<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Output;

interface OutputInterface
{
    /**
     * @param string $char
     * @param int    $repeat
     * @return $this
     */
    public function hr(string $char = '-', int $repeat = 50): self;

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