<?php
declare(strict_types=1);

namespace Mirakl\Connector\Setup\Patch\Data;

use Mirakl\Api\Helper\Config as ApiConfig;
use Mirakl\Connector\Setup\Patch\AbstractDefaultConfigApplier;

class InitApiAuthMethodConfig extends AbstractDefaultConfigApplier
{
    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $this->setup->startSetup();

        if (!$this->isFreshInstall->execute()) {
            $this->configWriter->save(ApiConfig::XML_PATH_AUTH_METHOD, 'api_key');
        }

        $this->setup->endSetup();
    }
}