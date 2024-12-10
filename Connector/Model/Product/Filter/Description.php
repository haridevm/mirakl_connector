<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Filter;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Helper\Output;

class Description implements FilterInterface
{
    /**
     * @var Output
     */
    private $output;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param Output $output
     * @param CatalogHelper $catalogHelper
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        Output $output,
        CatalogHelper $catalogHelper,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->output = $output;
        $this->catalogHelper = $catalogHelper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function filter($value)
    {
        $description = (string) $value;

        if (!empty($description)) {
            $attribute = $this->getAttribute();
            if ($attribute->getIsHtmlAllowedOnFront()
                && $attribute->getIsWysiwygEnabled()
                && $this->output->isDirectivesExists($description))
            {
                $description = $this->catalogHelper->getPageTemplateProcessor()->filter($description);
            }
        }

        return $description;
    }

    /**
     * @return ProductAttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttribute(): ProductAttributeInterface
    {
        return $this->attributeRepository->get('description');
    }
}
