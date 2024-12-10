<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;

use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter\EntityAdapterInterface;

class Importer extends \Magento\ImportExport\Model\Import implements ImporterInterface
{
    /**
     * @var int
     */
    protected $executionTime;

    /**
     * @inheritdoc
     */
    public function import(DataSourceInterface $dataSource): bool
    {
        $dataSource->getIterator()->rewind();
        $this->setDataSource($dataSource);

        $start = microtime(true);
        $result = $this->importSource();
        $this->executionTime = (int) round(microtime(true) - $start);

        $dataSource->clean();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getOutput(): string
    {
        return $this->getFormatedLogTrace();
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->getErrorAggregator()->getAllErrors();
    }

    /**
     * @inheritdoc
     */
    public function getExecutionTime(): int
    {
        return $this->executionTime;
    }

    /**
     * @param DataSourceInterface $dataSource
     */
    public function setDataSource(DataSourceInterface $dataSource)
    {
        $this->_importData = $dataSource;
        $this->_getEntityAdapter()->setDataSource($dataSource);
    }

    /**
     * @inheritdoc
     */
    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            try {
                $this->_entityAdapter = $this->_entityFactory->create(EntityAdapterInterface::class);
                $this->_entityAdapter->setParameters($this->getData());
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                throw new \Exception($e->getMessage());
            }
        }

        return $this->_entityAdapter;
    }
}
