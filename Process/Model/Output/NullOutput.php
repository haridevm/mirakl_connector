<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Output;

class NullOutput extends AbstractOutput
{
    /**
     * @inheritdoc
     */
    public function display($str): self
    {
        return $this;
    }
}
