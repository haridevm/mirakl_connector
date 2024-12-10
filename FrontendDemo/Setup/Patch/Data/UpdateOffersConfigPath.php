<?php
declare(strict_types=1);

namespace Mirakl\FrontendDemo\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateOffersConfigPath implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param ModuleDataSetupInterface $setup
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
        $setup = $this->setup;
        $setup->startSetup();

        $connection = $setup->getConnection();
        $configTable = $setup->getTable('core_config_data');

        $oldKey = \Mirakl\FrontendDemo\Helper\Config::XML_PATH_AUTO_REMOVE_OFFERS;
        $newKey = \Mirakl\Connector\Helper\Config::XML_PATH_AUTO_REMOVE_OFFERS;

        $select = $connection->select()
            ->from($configTable, 'config_id')
            ->where('path = ?', $newKey)
            ->limit(1);

        if (false === $connection->fetchOne($select)) {
            $where = ['path = ?' => $oldKey];
            $bind = ['path' => $newKey];
            $connection->update($configTable, $bind, $where);
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