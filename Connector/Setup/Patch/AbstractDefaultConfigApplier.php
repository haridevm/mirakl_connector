<?php

declare(strict_types=1);

namespace Mirakl\Connector\Setup\Patch;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Connector\Model\Connector\IsFreshInstallInterface;

abstract class AbstractDefaultConfigApplier implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var IsFreshInstallInterface
     */
    protected $isFreshInstall;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param IsFreshInstallInterface  $isFreshInstall
     * @param WriterInterface          $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        IsFreshInstallInterface $isFreshInstall,
        WriterInterface $configWriter
    ) {
        $this->setup = $setup;
        $this->isFreshInstall = $isFreshInstall;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
