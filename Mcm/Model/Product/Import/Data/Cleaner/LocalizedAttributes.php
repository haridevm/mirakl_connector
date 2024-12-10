<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

use Mirakl\Mci\Model\Product\Attribute\AttributeUtil;
use Mirakl\Mcm\Helper\Config;

class LocalizedAttributes implements CleanerInterface
{
    // This field will register all localized data
    public const I18N_FIELD = 'mirakl_localized_attributes';

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
     * @return string
     */
    private function getDefaultLocale(): string
    {
        return $this->config->getLocale();
    }

    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        $data[self::I18N_FIELD] = [];
        $defaultLocale = $this->getDefaultLocale();

        foreach (array_keys($data) as $attrCode) {
            // Parse the attribute code to retrieve the locale if present
            $attrInfo = AttributeUtil::parse($attrCode);

            if (!$attrInfo->isLocalized()) {
                continue; // Do not modify unlocalized field
            }

            $value = trim($data[$attrCode]);

            // Remove the original field <attribute_code>-<locale>
            unset($data[$attrCode]);

            if ('' === $value) {
                continue; // Empty values are useless in this context
            }

            if ($attrInfo->getLocale() !== $defaultLocale) {
                // Register localized values in an array as [<locale>][<attribute_code>] = <value>
                $data[self::I18N_FIELD][$attrInfo->getLocale()][$attrInfo->getCode()] = $value;
            } elseif (empty($data[$attrInfo->getCode()])) {
                // Transform <attribute_code>-<locale> to <attribute_code> if <locale> is the default locale
                $data[$attrInfo->getCode()] = $value;
            }
        }
    }
}
