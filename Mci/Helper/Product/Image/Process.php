<?php

declare(strict_types=1);

namespace Mirakl\Mci\Helper\Product\Image;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Mirakl\Mci\Helper\Product\Image as ImageHelper;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\Process as ProcessModel;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

class Process extends AbstractHelper
{
    public const CODE = 'IMAGE_IMPORT';
    public const PROCESS_NAME = 'Products images import';

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    protected $processResourceFactory;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @param Context                $context
     * @param ProcessFactory         $processFactory
     * @param ProcessResourceFactory $processResourceFactory
     * @param ImageHelper            $imageHelper
     */
    public function __construct(
        Context $context,
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        ImageHelper $imageHelper
    ) {
        parent::__construct($context);
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param int      $limit
     * @param int|null $timeout
     * @param string   $type
     * @param string   $status
     * @return ProcessModel
     */
    public function createImportProcess(
        $limit,
        $timeout = null,
        $type = ProcessModel::TYPE_ADMIN,
        $status = ProcessModel::STATUS_PENDING
    ) {
        $process = $this->processFactory->create();
        $process->setType($type)
            ->setName(self::PROCESS_NAME)
            ->setCode(self::CODE)
            ->setStatus($status)
            ->setHelper(ImageHelper::class)
            ->setMethod('runByProductIds');

        if ($timeout) {
            $process->setTimeout(abs(intval($timeout)));
        }

        $collection = $this->imageHelper->getProductsToProcess();
        $productIds = $collection->getAllIds((int) $limit);
        $collection->addIdFilter($productIds);

        $connection = $collection->getConnection();

        try {
            $connection->beginTransaction();

            $this->imageHelper->markProductsImagesAsProcessing($collection);

            $process->setParams([$productIds]);
            $this->processResourceFactory->create()->save($process);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $process->fail($e->getMessage());
        }

        return $process;
    }
}
