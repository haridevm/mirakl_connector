<?php

declare(strict_types=1);

namespace Mirakl\Core\Console\Command;

use Mirakl\Core\Model\Cli\AclAuthorizationManager;

/**
 * @property \Magento\Framework\App\State                       $appState
 * @property \Magento\Framework\ObjectManagerInterface          $objectManager
 * @property \Magento\Framework\ObjectManager\ConfigInterface   $configManager
 */
trait CommandTrait
{
    /**
     * Initialize a specific ACL policy to allow products creation from CLI
     *
     * @return void
     */
    protected function initAuthorization()
    {
        /** @var AclAuthorizationManager $cliAuthorizationManager */
        // @phpstan-ignore-next-line
        $cliAuthorizationManager = $this->objectManager->get(AclAuthorizationManager::class);
        $cliAuthorizationManager->setIsCliMode(true);
        $cliAuthorizationManager->setIsCliAuthorized(true);
    }

    /**
     * Set area code in safe mode
     *
     * @param string $code
     */
    public function setAreaCode($code)
    {
        try {
            $area = $this->appState->getAreaCode();
        } catch (\Exception $e) {
            // Ignore potential exception
        } finally {
            if (empty($area)) {
                $this->appState->setAreaCode($code);
            }
        }
    }
}
