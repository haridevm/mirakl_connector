<?php
declare(strict_types=1);

namespace Mirakl\Mci\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Mci\Helper\Config as MciConfig;

class SetDecimalAttributesPrecision implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var ProductMetadataInterface
     */
    private $magentoMetadata;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ProductMetadataInterface $magentoMetadata
     * @param WriterInterface          $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductMetadataInterface $magentoMetadata,
        WriterInterface $configWriter
    ) {
        $this->setup = $setup;
        $this->magentoMetadata = $magentoMetadata;
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        if (version_compare($this->magentoMetadata->getVersion(), '2.3.3', '>=')) {
            $this->configWriter->save(MciConfig::XML_PATH_DEFAULT_DECIMAL_ATTRIBUTES_PRECISION, 6); // default decimal precision for Magento 2.3.3+
        } else {
            $this->configWriter->save(MciConfig::XML_PATH_DEFAULT_DECIMAL_ATTRIBUTES_PRECISION, 4);
        }

        $setup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}