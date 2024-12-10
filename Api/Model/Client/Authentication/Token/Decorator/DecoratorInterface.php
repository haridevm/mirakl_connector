<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Token\Decorator;

interface DecoratorInterface
{
    /**
     * @param string $token
     * @return string
     */
    public function decorate(string $token): string;
}