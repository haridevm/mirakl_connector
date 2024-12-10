<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method;

interface MethodPoolInterface
{
    /**
     * @param string $code
     * @return MethodInterface
     */
    public function get(string $code): MethodInterface;

    /**
     * @return MethodInterface[]
     */
    public function getAll(): array;
}