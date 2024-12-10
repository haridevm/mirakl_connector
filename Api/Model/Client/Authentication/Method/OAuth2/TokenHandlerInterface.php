<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method\OAuth2;

interface TokenHandlerInterface
{
    /**
     * @return void
     */
    public function refresh(): void;

    /**
     * @return void
     */
    public function reset(): void;
}