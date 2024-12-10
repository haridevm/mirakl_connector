<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\Import\Data as DataSourceModel;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class Json extends DataSourceModel implements DataSourceInterface
{
    /**
     * @var JsonSerializer
     */
    private $json;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $behavior;

    /**
     * @var int
     */
    private $bunchSize;

    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * @param Context        $context
     * @param Data           $jsonHelper
     * @param JsonSerializer $json
     * @param string|null    $connectionName
     * @param string         $entityType
     * @param string         $behavior
     * @param int            $bunchSize
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        JsonSerializer $json,
        $connectionName = null,
        $entityType = 'catalog_product',
        $behavior = Import::BEHAVIOR_APPEND,
        $bunchSize = 100
    ) {
        parent::__construct(
            $context,
            $jsonHelper,
            $connectionName
        );
        $this->json = $json;
        $this->entityType = $entityType;
        $this->behavior = $behavior;
        $this->bunchSize = $bunchSize;
    }

    /**
     * @return \SplFileObject
     */
    public function getFile(): \SplFileObject
    {
        if (null === $this->file) {
            $this->setFile(new \SplTempFileObject());
        }

        return $this->file;
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Traversable
    {
        return $this->getFile();
    }

    /**
     * @param \SplFileObject $file
     */
    public function setFile(\SplFileObject $file): void
    {
        $this->file = $file;
    }

    /**
     * @param array $ids
     * @return string
     */
    public function getBehavior($ids = null): string
    {
        return $this->behavior;
    }

    /**
     * @param array $ids
     * @return string
     */
    public function getEntityTypeCode($ids = null): string
    {
        return $this->entityType;
    }

    /**
     * @inheritdoc
     */
    public function getNextBunch(): ?array
    {
        $file = $this->getFile();

        if ($file->eof()) {
            // If processed all the file rows, rewind to the beginning for next bunch processors
            $file->rewind();

            return null;
        }

        $bunch = [];

        while ($file->valid() && count($bunch) < $this->bunchSize) {
            $row = $file->fgets();
            if (!empty($row)) {
                $bunch[$file->key()] = $this->json->unserialize($row);
            }
        }

        return $bunch;
    }

    /**
     * @inheritdoc
     */
    public function getNextUniqueBunch($ids = null)
    {
        return $this->getNextBunch();
    }

    /**
     * @inheritoc
     */
    public function write(array $data): int
    {
        return $this->getFile()->fwrite($this->json->serialize($data) . "\n");
    }

    /**
     * @inheritdoc
     */
    public function clean(): void
    {
        unset($this->file);
    }

    /**
     * @return void
     */
    public function cleanBunches()
    {
        $this->clean();
    }
}