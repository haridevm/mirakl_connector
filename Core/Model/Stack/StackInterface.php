<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\Stack;

interface StackInterface extends \Iterator, \Countable
{
    /**
     * @param string $key
     * @param mixed  $value
     */
    public function add(string $key, $value): void;

    /**
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array;
}
