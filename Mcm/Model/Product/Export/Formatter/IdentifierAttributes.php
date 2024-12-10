<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

use Mirakl\Mcm\Helper\Config;

class IdentifierAttributes implements FormatterInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data): void
    {
        // Format identifier attributes with multivalues if used
        $identifierAttributes = $this->config->getMcmIdentifiersAttributes();
        $separator = $this->config->getMultivalueAttributesSeparator();

        foreach ($identifierAttributes as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = explode($separator, $data[$key]);
            }
        }
    }
}
