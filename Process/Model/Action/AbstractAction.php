<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

use Magento\Framework\DataObject;

abstract class AbstractAction extends DataObject implements ActionInterface
{
    /**
     * @inheritdoc
     */
    public function addParams(array $params): void
    {
        $this->addData($params);
    }

    /**
     * @inheritdoc
     */
    public function getParams(): array
    {
        return $this->getData();
    }

    /**
     * @inheritdoc
     */
    public function setParams(array $params): void
    {
        $this->setData($params);
    }
}