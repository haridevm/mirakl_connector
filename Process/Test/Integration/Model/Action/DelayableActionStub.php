<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\DelayableTrait;

class DelayableActionStub extends ActionStub
{
    use DelayableTrait;
}
