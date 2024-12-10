<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Process;

class ClosureActionStub extends AbstractAction
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @param \Closure $closure
     * @param array    $data
     */
    public function __construct(\Closure $closure, array $data = [])
    {
        parent::__construct($data);
        $this->closure = $closure;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Simple action stub';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        return $this->closure->__invoke($process, ...$params);
    }
}
