<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mci\Model\Product\Attribute\ProductAttributesFinder;
use Mirakl\Mcm\Helper\Config;
use Mirakl\Mcm\Model\Product\Import\Data\Cleaner;
use Mirakl\Mcm\Model\Product\Import\Data\Generator\GeneratorInterface;

class I18n implements FormatterInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var GeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var ProductAttributesFinder
     */
    private $attributesFinder;

    /**
     * @param Config $config
     * @param GeneratorInterface $urlGenerator
     * @param ProductAttributesFinder $attributesFinder
     */
    public function __construct(
        Config $config,
        GeneratorInterface $urlGenerator,
        ProductAttributesFinder $attributesFinder
    ) {
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $this->attributesFinder = $attributesFinder;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data, ?StoreInterface $store = null): void
    {
        if (null === $store) {
            return;
        }

        $dataLocalized = $data[Cleaner\LocalizedAttributes::I18N_FIELD];
        $locale = $this->config->getLocale($store);

        if (empty($dataLocalized[$locale])) {
            return;
        }

        $setId = (int) $data['attribute_set_id'];
        $typeId = $data['product_type'];

        $data = array_merge($data, $dataLocalized[$locale]);

        $requiredAttributes = array_merge(
            array_keys($this->getRequiredAttributes($setId, $typeId)), // Include required attributes
            array_keys($dataLocalized[$locale]), // Include localized data
            ['sku', 'name', 'product_type', '_attribute_set'] // Required fields for translation
        );

        $data = array_intersect_key($data, array_flip($requiredAttributes));

        // Add Magento required fields
        $data['_store'] = $store->getCode();
        $data['price'] = 0;
        $data['url_key'] = $this->urlGenerator->generate($data);
    }

    /**
     * @param int $setId
     * @param string $typeId
     * @return array
     */
    private function getRequiredAttributes(int $setId, string $typeId): array
    {
        return array_filter($this->attributesFinder->findBySetId($setId),
            function ($attr) use ($typeId) {
                return $attr->getIsRequired()
                    && $attr->getIsVisible()
                    && $attr->getBackendType() !== 'static'
                    && (empty($attr->getApplyTo()) || in_array($typeId, $attr->getApplyTo()));
            }
        );
    }
}