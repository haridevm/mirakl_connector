<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client;

use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Method\MethodInterface;
use Mirakl\Api\Model\Client\Authentication\Method\MethodPoolInterface;

class ClientSettings implements ClientSettingsInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var MethodPoolInterface
     */
    private $methodPool;

    /**
     * @param Config              $config
     * @param MethodPoolInterface $methodPool
     */
    public function __construct(Config $config, MethodPoolInterface $methodPool)
    {
        $this->config = $config;
        $this->methodPool = $methodPool;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @inheritdoc
     */
    public function getApiUrl(): string
    {
        return $this->config->getApiUrl();
    }

    /**
     * @inheritdoc
     */
    public function getAuthMethod(): MethodInterface
    {
        $code = $this->config->getAuthMethod();

        return $this->methodPool->get($code);
    }

    /**
     * @inheritdoc
     */
    public function getConnectTimeout(): int
    {
        return $this->config->getConnectTimeout();
    }
}
