<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Output\Formatter;

use Magento\Framework\ObjectManagerInterface;
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
     * @param string $type
     * @return FormatterInterface
     */
    public function create(string $type): FormatterInterface
    {
        $instanceName = __NAMESPACE__ . '\\' . pascalize($type);

        if (!class_exists($instanceName)) {
            throw new \InvalidArgumentException('Could not find output formatter for type ' . $type);
        }

        return $this->objectManager->create($instanceName);
    }
}
