<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method;

interface ExpirableTokenInterface
{
    /**
     * @return bool
     */
    public function isTokenExpired(): bool;

    /**
     * @return \DateTimeInterface|null
     */
    public function getTokenExpirationDate(): ?\DateTimeInterface;
}
