<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Output;

class Cli extends AbstractOutput
{
    /**
     * {@inheritdoc}
     */
    public function display($str): self
    {
        if (!$this->process->getQuiet()) {
            if ($this->process->getParentId()) {
                $str = "    $str";
            }
            echo $this->format($str) . PHP_EOL;
            @ob_flush();
        }

        return $this;
    }
}
