<?php
declare(strict_types=1);

namespace Mirakl\Connector\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateTranslationStoreConfigPath implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param ModuleDataSetupInterface  $setup
     */
    public function __construct(ModuleDataSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $this->setup->startSetup();

        $connection = $this->setup->getConnection();

        $configTable  = $this->setup->getTable('core_config_data');
        $updateConfig = [
            'mirakl_mci/general/translation_store' => 'mirakl_connector/general/translation_store',
            'mirakl_mci/general/locale_codes_for_labels_translation' => 'mirakl_connector/general/locale_codes_for_labels_translation',
        ];

        foreach ($updateConfig as $oldKey => $newKey) {
            $where = ['path = ?' => $oldKey];
            $bind  = ['path' => $newKey];
            $connection->update($configTable, $bind, $where);
            $connection->delete($configTable, $where);
        }

        $this->setup->endSetup();
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