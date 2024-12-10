<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Adapter;

use Magento\Framework\Filesystem\File\ReadInterface;
use Mirakl\Process\Model\Process;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var ReadInterface
     */
    protected $file;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @return ReadInterface|null
     */
    public function getFile(): ?ReadInterface
    {
        return $this->file;
    }

    /**
     * @param ReadInterface $file
     */
    public function setFile(ReadInterface $file)
    {
        $this->file = $file;
    }

    /**
     * @return Process|null
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param Process $process
     */
    public function setProcess(Process $process)
    {
        $this->process = $process;
    }
}
