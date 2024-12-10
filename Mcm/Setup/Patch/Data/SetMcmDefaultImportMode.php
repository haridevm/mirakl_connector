<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Setup\Patch\Data;

use Mirakl\Connector\Setup\Patch\AbstractDefaultConfigApplier;
use Mirakl\Mcm\Helper\Config as McmConfig;

class SetMcmDefaultImportMode extends AbstractDefaultConfigApplier
{
    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $this->setup->startSetup();
        if ($this->isFreshInstall->execute()) {
            // Set default MCM products import mode to 'bulk'
            $this->configWriter->save(McmConfig::XML_PATH_MCM_PRODUCTS_IMPORT_MODE, 'bulk');
        }

        $this->setup->endSetup();
    }
}
