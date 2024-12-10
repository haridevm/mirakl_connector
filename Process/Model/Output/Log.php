<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Output;

class Log extends AbstractOutput
{
    /**
     * {@inheritdoc}
     */
    public function display($str): self
    {
        $this->logger->info($this->format($str));

        return $this;
    }
}
