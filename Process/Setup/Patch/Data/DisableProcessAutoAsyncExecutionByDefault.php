<?php
declare(strict_types=1);

namespace Mirakl\Process\Setup\Patch\Data;

use Mirakl\Connector\Setup\Patch\AbstractDefaultConfigApplier;
use Mirakl\Process\Helper\Config as ProcessConfig;

class DisableProcessAutoAsyncExecutionByDefault extends AbstractDefaultConfigApplier
{
    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $this->setup->startSetup();

        if ($this->isFreshInstall->execute()) {
            // We set process auto async execution by default to NO
            $this->configWriter->save(ProcessConfig::XML_PATH_AUTO_ASYNC_EXECUTION, 0);
        }

        $this->setup->endSetup();
    }
}