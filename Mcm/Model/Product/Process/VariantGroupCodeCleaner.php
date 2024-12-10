<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Process;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Process\Model\Process;

class VariantGroupCodeCleaner
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param EventManagerInterface    $eventManager
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        EventManagerInterface $eventManager,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->eventManager = $eventManager;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param Process $process
     * @param string  $vgc
     * @param array   $variantIds
     * @return void
     */
    public function execute(Process $process, $vgc, array $variantIds)
    {
        if (empty($variantIds)) {
            $process->output(__('No variant products provided for cleaning'));
        } else {
            $process->output(__("Cleaning variant group code '%1' of the following products:", $vgc));
            $process->output(implode(', ', $variantIds));

            $collection = $this->productCollectionFactory->create();
            $collection->getConnection()->update(
                $collection->getMainTable(),
                [McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE => null],
                ['entity_id IN (?)' => $variantIds]
            );

            $process->output('Done!');

            $this->eventManager->dispatch('mirakl_mcm_vgc_clean_after', [
                'process'     => $process,
                'vgc'         => $vgc,
                'product_ids' => $variantIds,
            ]);
        }
    }
}
