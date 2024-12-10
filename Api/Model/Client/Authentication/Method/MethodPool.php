<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method;

use Magento\Framework\Exception\NoSuchEntityException;

class MethodPool implements MethodPoolInterface
{
    /**
     * @var MethodInterface[]
     */
    private array $methods;

    /**
     * @param array $methods
     */
    public function __construct(array $methods = [])
    {
        $this->methods = $methods;
    }

    /**
     * @inheritdoc
     */
    public function get(string $code): MethodInterface
    {
        if (isset($this->methods[$code])) {
            return $this->methods[$code];
        }

        throw new NoSuchEntityException(__('Could not find method with code %1', $code));
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->methods;
    }
}