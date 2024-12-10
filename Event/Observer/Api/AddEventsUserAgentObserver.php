<?php
declare(strict_types=1);

namespace Mirakl\Event\Observer\Api;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Core\Client\AbstractApiClient;
use Mirakl\Event\Model\Event;

class AddEventsUserAgentObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $type = $observer->getEvent()->getType();
        $helper = $observer->getEvent()->getHelper();
        /** @var AbstractApiClient $client */
        $client = $helper->getClient();
        $userAgent = $client->getUserAgent();
        $userAgent .= sprintf(' Using-Events-Module/Yes Event-Export-Type/%s', Event::getShortTypeLabel($type));
        $client->setUserAgent($userAgent);
    }
}
