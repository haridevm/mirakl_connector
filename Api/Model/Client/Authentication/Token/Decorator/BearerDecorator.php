<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Token\Decorator;

class BearerDecorator implements DecoratorInterface
{
    /**
     * @inheritdoc
     */
    public function decorate(string $token): string
    {
        if (false === stripos($token, 'Bearer ')) {
            $token = "Bearer $token";
        }

        return $token;
    }
}