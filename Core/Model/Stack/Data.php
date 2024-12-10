<?php
declare(strict_types=1);

namespace Mirakl\Core\Model\Stack;

class Data implements StackInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @inheritdoc
     */
    public function add(string $key, $value): void
    {
        if (empty($this->data[$key])) {
            $this->data[$key] = [];
        }

        $this->data[$key][] = $value;
    }

    /**
     * @inheritdoc
     */
    public function get(string $key): ?array
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    /**
     * @inheritdoc
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return (bool) current($this->data);
    }
}
