<?php
declare(strict_types=1);

namespace Mirakl\Mci\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Mci\Helper\Config as MciConfig;

class UpdateImageImportConfigPaths implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var
     */
    private $mciConfig;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param MciConfig                $mciConfig
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        MciConfig $mciConfig
    ) {
        $this->setup = $setup;
        $this->mciConfig = $mciConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        $mapping = [
            MciConfig::XML_PATH_MCI_IMAGE_MAX_SIZE                 => 'mirakl_mci/images_import/image_max_size',
            MciConfig::XML_PATH_MCI_IMAGES_IMPORT_LIMIT            => 'mirakl_mci/images_import/limit',
            MciConfig::XML_PATH_MCI_IMAGES_IMPORT_HEADERS          => 'mirakl_mci/images_import/headers',
            MciConfig::XML_PATH_MCI_IMAGES_IMPORT_PROTOCOL_VERSION => 'mirakl_mci/images_import/protocol_version',
            MciConfig::XML_PATH_MCI_IMAGES_IMPORT_TIMEOUT          => 'mirakl_mci/images_import/timeout',
        ];

        foreach ($mapping as $newPath => $oldPath) {
            $oldValue = $this->mciConfig->getValue($oldPath);

            if ($oldValue !== null && $oldValue !== '') {
                $this->mciConfig->setValue($newPath, $oldValue);
            }
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