<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\RetryableTrait;

class RetryableActionStub extends ActionStub
{
    use RetryableTrait;
}
