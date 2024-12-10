<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mcm\Model\Product\Import\Repository;

class AttributeSet implements FormatterInterface
{
    /**
     * @var Repository\AttributeSet
     */
    private $attrSetRepository;

    /**
     * @param Repository\AttributeSet $attrSetRepository
     */
    public function __construct(Repository\AttributeSet $attrSetRepository)
    {
        $this->attrSetRepository = $attrSetRepository;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data, ?StoreInterface $store = null): void
    {
        $data['_attribute_set'] = $this->attrSetRepository->get($data['attribute_set_id']);
    }
}