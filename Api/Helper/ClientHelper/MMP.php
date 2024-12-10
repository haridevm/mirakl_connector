<?php

declare(strict_types=1);

namespace Mirakl\Api\Helper\ClientHelper;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\MMP\Common\Domain\Collection\Locale\LocaleCollection;
use Mirakl\MMP\Common\Request\Locale\GetLocalesRequest;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 *
 * @method \Mirakl\MMP\Front\Client\FrontApiClient getClient()
 */
class MMP extends AbstractClientHelper
{
    public const AREA_NAME = 'MMP';

    /**
     * @var array
     */
    protected $activeLocales;

    /**
     * (L01) Get active locales in Mirakl platform
     *
     * @return LocaleCollection
     */
    public function getActiveLocales()
    {
        if (null === $this->activeLocales) {
            $cacheKey = 'mirakl_get_active_locales';
            if ($locales = $this->cache->load($cacheKey)) {
                $this->activeLocales = unserialize($locales); // phpcs:ignore
            } else {
                \Magento\Framework\Profiler::start(__METHOD__);
                $this->activeLocales = $this->send(new GetLocalesRequest());
                \Magento\Framework\Profiler::stop(__METHOD__);

                $this->cache->save(
                    serialize($this->activeLocales), // phpcs:ignore
                    $cacheKey,
                    ['MIRAKL', \Magento\Framework\App\Cache\Type\Config::CACHE_TAG]
                );
            }
        }

        return $this->activeLocales;
    }

    /**
     * @inheritdoc
     */
    protected function getArea()
    {
        return self::AREA_NAME;
    }

    /**
     * Returns current Mirakl environment version
     *
     * @return string
     * @throws LocalizedException
     */
    public function getVersion()
    {
        $client = $this->getClient();

        if (!$client->getBaseUrl() || !$client->getApiKey()) {
            throw new LocalizedException(__('Please specify your Mirakl API parameters.'));
        }

        return $client->getVersion()->getVersion();
    }

    /**
     * Verify that specified locale exists in Mirakl. If not, reset it.
     *
     * @param string $locale
     * @return string|null
     */
    protected function validateLocale($locale)
    {
        try {
            $locales = $this->getActiveLocales()->walk('getCode');
        } catch (\Exception $e) {
            $this->_logger->critical($e);

            return null;
        }

        return in_array($locale, $locales) ? $locale : null;
    }
}
