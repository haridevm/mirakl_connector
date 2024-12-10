<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

class ActionList implements ActionListInterface
{
    /**
     * @var ActionInterface[]
     */
    private array $actions;

    /**
     * @var bool
     */
    private bool $areParamsChainable;

    /**
     * @param array $actions
     * @param bool  $areParamsChainable
     */
    public function __construct(array $actions = [], bool $areParamsChainable = true)
    {
        $this->actions = $actions;
        $this->areParamsChainable = $areParamsChainable;
    }

    /**
     * @inheritdoc
     */
    public function areParamsChainable(): bool
    {
        return $this->areParamsChainable;
    }

    /**
     * @inheritdoc
     */
    public function get(array $params = []): \Generator
    {
        foreach ($this->actions as $action) {
            yield $action;
        }
    }
}