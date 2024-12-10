<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Product\Offer;

use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Mirakl\MMP\Common\Domain\Offer\Shipping\DeliveryTime as OfferDeliveryTime;
use Mirakl\MMP\Common\Domain\Shipping\DeliveryTime;

class ShippingDate extends Template
{
    /**
     * @var DeliveryTime|OfferDeliveryTime
     */
    private $deliveryTime;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param Template\Context  $context
     * @param TimezoneInterface $timezone
     * @param array             $data
     */
    public function __construct(
        Template\Context $context,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->timezone = $timezone;
        parent::__construct($context, $data);
    }

    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_template = 'product/offer/shipping_date.phtml';

    /**
     * @return DeliveryTime|OfferDeliveryTime|null
     */
    public function getDeliveryTime()
    {
        return $this->deliveryTime;
    }

    /**
     * @param DeliveryTime|OfferDeliveryTime|null $deliveryTime
     * @return $this
     */
    public function setDeliveryTime($deliveryTime)
    {
        $this->deliveryTime = $deliveryTime;

        return $this;
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    public function format(\DateTime $date): string
    {
        if ($this->isDisplayHourOnly($date)) {
            return $this->_localeDate->formatDateTime(
                \Mirakl\date_format($date),
                \IntlDateFormatter::NONE
            );
        }

        $pattern = __('MMMM d');
        return $this->_localeDate->formatDateTime(
            \Mirakl\date_format($date),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            null,
            null,
            $pattern
        );
    }

    /**
     * @param \DateTime $date
     * @return bool
     */
    public function isDisplayHourOnly(\DateTime $date): bool
    {
        $now = $this->timezone->date();
        $storeTimezone = $this->timezone->getConfigTimezone('store', $this->_storeManager->getStore()->getCode());
        $dateInStoreTimezone = $date->setTimezone(new \DateTimeZone($storeTimezone));

        if ($dateInStoreTimezone->format('Ymd') == $now->format('Ymd')) {
            return true;
        }

        return false;
    }

    /**
     * @param string|null      $cutOffTime
     * @param \DateTime|string $cutOffNextDate
     * @return Phrase|null
     */
    public function getCutOffTime(?string $cutOffTime, $cutOffNextDate): ?Phrase
    {
        if (!$cutOffTime || !$cutOffNextDate) {
            return null;
        }

        $date = new \DateTime($cutOffTime);
        $now = new \DateTime('now -1 minute');

        if ($now >= $date) {
            $date = $cutOffNextDate instanceof \DateTimeInterface ? $cutOffNextDate : new \DateTime($cutOffNextDate);
        }

        $interval = $date->diff($now);
        $hours = (int) $interval->format('%h');
        $minutes = (int) $interval->format('%i');

        if (!$hours) {
            return __('%1min', $minutes);
        }

        if (!$minutes) {
            return __('%1hr', $hours);
        }

        return __('%1hr %2min', $hours, $minutes);
    }
}
