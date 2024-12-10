<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirakl\Api\Model\Client\Authentication\Method\MethodPoolInterface;

class AuthMethod implements OptionSourceInterface
{
    /**
     * @var MethodPoolInterface
     */
    private $methodPool;

    /**
     * @param MethodPoolInterface $methodPool
     */
    public function __construct(MethodPoolInterface $methodPool)
    {
        $this->methodPool = $methodPool;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [[
            'value' => '',
            'label' => __('-- Please Select --'),
        ]];

        foreach ($this->methodPool->getAll() as $code => $method) {
            $options[] = [
                'value' => $code,
                'label' => $method->getLabel(),
            ];
        }

        return $options;
    }
}
