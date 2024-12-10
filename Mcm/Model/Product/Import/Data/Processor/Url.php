<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Mirakl\Mcm\Model\Product\Import\Data\Generator\GeneratorInterface;

class Url implements ProcessorInterface
{
    /**
     * @var GeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param GeneratorInterface $urlGenerator
     */
    public function __construct(GeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if (null === $product) {
            $data['url_key'] = $this->urlGenerator->generate($data);
        }
    }
}