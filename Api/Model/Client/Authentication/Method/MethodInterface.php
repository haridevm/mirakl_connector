<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method;

interface MethodInterface
{
    /**
     * Constant for value of an obscured API key
     */
    public const OBSCURED_KEY = '******';

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string|null
     */
    public function getAuthHeader(): ?string;

    /**
     * @param array $params
     * @return bool
     */
    public function testConnection(array $params): bool;

    /**
     * @throws \Exception
     */
    public function validate(): void;
}
