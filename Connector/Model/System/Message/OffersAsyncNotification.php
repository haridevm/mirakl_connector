<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use Mirakl\Connector\Helper\Config;

class OffersAsyncNotification implements MessageInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param UrlInterface $urlBuilder
     * @param Config       $config
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Config $config
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity()
    {
        return 'MIRAKL_OFFERS_ASYNC';
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayed()
    {
        return !$this->config->isIgnoreNewImportNotification();
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        $configUrl = $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/mirakl_sync');
        $ignoreUrl = $this->urlBuilder->getUrl('mirakl/offer/ignoreNotification');

        return __(
            '<strong>Mirakl offers import has changed. Synchronization now uses Mirakl asynchronous API OF52/OF53/OF54.</strong><br>'.
            'It is still possible (but not recommended) to switch back to the legacy offers import with API OF51.<br>' .
            'Please go to <a href="%1">Mirakl > Configuration > Synchronization</a> for more information. <a href="%2">Ignore</a>',
            $configUrl, $ignoreUrl
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}