<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mirakl\Mcm\Model\Product\Import\Loader\Customizer\CustomizerInterface;
use Mirakl\Mcm\Model\Product\Import\Loader\Decorator\DecoratorInterface;

class Product implements LoaderInterface
{
    /**
     * @var CustomizerInterface[]
     */
    private $customizers;

    /**
     * @var DecoratorInterface[]
     */
    private $decorators;

    /**
     * @param CustomizerInterface[] $customizers
     * @param DecoratorInterface[] $decorators
     */
    public function __construct(
        array $customizers = [],
        array $decorators = []
    ) {
        $this->customizers = $customizers;
        $this->decorators = $decorators;
    }

    /**
     * @inheritdoc
     */
    public function load(Collection $collection): array
    {
        // Customize the collection (add some columns for example)
        foreach ($this->customizers as $customizer) {
            $customizer->customize($collection);
        }

        // Fetch products from database as array
        $data = $collection->getConnection()->fetchAll($collection->getSelect());

        if (!empty($data)) {
            // Add some useful data to the products array
            foreach ($this->decorators as $decorator) {
                $decorator->decorate($data);
            }
        }

        return $data;
    }
}