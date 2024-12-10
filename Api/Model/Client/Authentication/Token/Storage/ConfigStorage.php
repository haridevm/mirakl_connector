<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Token\Storage;

use Magento\Framework\Encryption\EncryptorInterface;
use Mirakl\Api\Helper\Config;

class ConfigStorage implements StorageInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var bool
     */
    private bool $encrypted;

    /**
     * @param Config             $config
     * @param EncryptorInterface $encryptor
     * @param string             $path
     * @param bool               $encrypted
     */
    public function __construct(
        Config $config,
        EncryptorInterface $encryptor,
        string $path,
        bool $encrypted = false
    ) {
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->path = $path;
        $this->encrypted = $encrypted;
    }

    /**
     * @inheritdoc
     */
    public function load(): ?string
    {
        $value = (string) $this->config->getRawValue($this->path);

        if ('' === $value) {
            return null;
        }

        return $this->encrypted
            ? $this->encryptor->decrypt($value)
            : $value;
    }

    /**
     * @inheritdoc
     */
    public function save(string $value): void
    {
        if ($this->encrypted) {
            $value = $this->encryptor->encrypt($value);
        }

        $this->config->setValue($this->path, $value);
    }

    /**
     * @inheritdoc
     */
    public function reset(): void
    {
        $this->config->deleteValue($this->path);
    }
}
