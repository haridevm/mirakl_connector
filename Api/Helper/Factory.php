<?php
declare(strict_types=1);

namespace Mirakl\Api\Helper;

use Magento\Framework\ObjectManagerInterface;
use Mirakl\Api\Helper\ClientHelper\AbstractClientHelper;
use function Mirakl\pascalize;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $name
     * @return AbstractClientHelper
     */
    public function get(string $name): AbstractClientHelper
    {
        $pieces = array_map(function ($piece) {
            return pascalize($piece);
        }, explode('/', $name));

        $class = __NAMESPACE__ . '\\' . implode('\\', $pieces);
        $helper = $this->objectManager->get($class);

        if (!$helper instanceof AbstractClientHelper) {
            throw new \InvalidArgumentException('Could not get API helper for name: ' . $name);
        }

        return $helper;
    }
}