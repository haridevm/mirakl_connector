<?php
declare(strict_types=1);

namespace Mirakl\Process\Ui\Component;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    use GetParentIdInRequestTrait;

    /**
     * {@inheirtdoc}
     */
    protected function prepareUpdateUrl(): void
    {
        parent::prepareUpdateUrl();

        $parentId = $this->getParentId();

        // Add the parent id in the url used to retrieved listing data in ajax
        $this->defineUpdateUrl($parentId);

        // Filter by parent id if needed or exclude process with parent
        $this->addParentFilter($parentId);
    }

    /**
     * @param int|null $parentId
     * @return void
     */
    protected function defineUpdateUrl(?int $parentId): void
    {
        if ($parentId) {
            $this->data['config']['update_url'] = sprintf(
                '%s%s/%s/',
                $this->data['config']['update_url'],
                'parent_id',
                $parentId
            );
        }
    }

    /**
     * @param int|null $parentId
     * @return void
     */
    protected function addParentFilter(?int $parentId): void
    {
        $filter = $this->filterBuilder->setField('parent_id');
        if ($parentId) {
            $filter->setValue($parentId)->setConditionType('eq');
        } else {
            $filter->setValue('')->setConditionType('null');
        }

        $this->addFilter($filter->create());
    }
}
