<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Mirakl\Api\Helper\Config;
use Mirakl\Core\Client\AbstractApiClient;
use Mirakl\Core\Helper\Data as CoreHelper;

class ClientFactory
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ClientSettingsInterface
     */
    private $clientSettings;

    /**
     * @param Config                   $config
     * @param CoreHelper               $coreHelper
     * @param ProductMetadataInterface $productMetadata
     * @param State                    $appState
     * @param ClientSettingsInterface  $clientSettings
     */
    public function __construct(
        Config $config,
        CoreHelper $coreHelper,
        ProductMetadataInterface $productMetadata,
        State $appState,
        ClientSettingsInterface $clientSettings
    ) {
        $this->config = $config;
        $this->coreHelper = $coreHelper;
        $this->productMetadata = $productMetadata;
        $this->appState = $appState;
        $this->clientSettings = $clientSettings;
    }

    /**
     * @param string $area
     * @return AbstractApiClient
     */
    public function create($area)
    {
        $apiUrl = $this->clientSettings->getApiUrl();
        $authMethod = $this->clientSettings->getAuthMethod();
        $apiKey = $authMethod->getAuthHeader();

        switch ($area) {
            case 'MMP':
                $instanceName = \Mirakl\MMP\Front\Client\FrontApiClient::class;
                break;
            case 'MCI':
                $instanceName = \Mirakl\MCI\Front\Client\FrontApiClient::class;
                break;
            case 'MCM':
                $instanceName = \Mirakl\MCM\Front\Client\FrontApiClient::class;
                break;
            default:
                throw new \InvalidArgumentException('Could not create API client for area ' . $area);
        }

        $client = new $instanceName($apiUrl, $apiKey);
        $this->init($client);

        return $client;
    }

    /**
     * @param AbstractApiClient $client
     */
    private function init(AbstractApiClient $client)
    {
        // Customize User-Agent
        $userAgent = sprintf(
            'Magento-%s/%s/%s Mirakl-Magento-Connector/%s %s',
            $this->productMetadata->getEdition(),
            $this->productMetadata->getVersion(),
            $this->getAreaCode() ?: 'unknown',
            $this->coreHelper->getVersion(),
            AbstractApiClient::getDefaultUserAgent()
        );

        $client->setUserAgent($userAgent);

        // Add a connection timeout
        $client->addOption('connect_timeout', $this->clientSettings->getConnectTimeout());

        // Disable API calls if needed
        if (!$this->clientSettings->isEnabled()) {
            $client->disable();
        }
    }

    /**
     * @return string|null
     */
    private function getAreaCode(): ?string
    {
        try {
            return $this->appState->getAreaCode();
        } catch (\Exception $e) {
            return null;
        }
    }
}
