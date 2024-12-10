<?php

declare(strict_types=1);

namespace Mirakl\Api\Helper;

use Mirakl\MMP\Common\Domain\Reason\ReasonType;
use Mirakl\MMP\FrontOperator\Domain\Collection\Reason\ReasonCollection;
use Mirakl\MMP\FrontOperator\Request\Reason\GetReasonsRequest;

class Reason extends ClientHelper\MMP
{
    /**
     * @var array
     */
    private $reasonsByType = [];

    /**
     * (RE01) Fetches reasons from Mirakl platform that can be used for opening incident or create a refund.
     *
     * @param string $locale
     * @return ReasonCollection
     */
    public function getReasons($locale = null)
    {
        $request = new GetReasonsRequest();
        $request->setLocale($this->validateLocale($locale));

        return $this->send($request);
    }

    /**
     * Returns reasons by type
     *
     * @param string $type
     * @param string $locale
     * @return ReasonCollection
     */
    public function getTypeReasons($type = ReasonType::INCIDENT_OPEN, $locale = null)
    {
        if (!isset($this->reasonsByType[$type])) {
            $reasons = $this->getReasons($locale);
            $reasonCollection = new ReasonCollection();

            /** @var \Mirakl\MMP\FrontOperator\Domain\Reason $reason */
            foreach ($reasons as $reason) {
                if ($reason->getType() === $type && $reason->getCustomerRight() === true) {
                    $reasonCollection->add($reason);
                }
            }

            $this->reasonsByType[$type] = $reasonCollection;
        }

        return $this->reasonsByType[$type];
    }

    /**
     * Fetches reasons for opening an incident
     *
     * @param string $locale
     * @return ReasonCollection
     */
    public function getOpenIncidentReasons($locale = null)
    {
        return $this->getTypeReasons(ReasonType::INCIDENT_OPEN, $locale);
    }

    /**
     * Fetches reasons for closing an incident
     *
     * @param string $locale
     * @return ReasonCollection
     */
    public function getCloseIncidentReasons($locale = null)
    {
        return $this->getTypeReasons(ReasonType::INCIDENT_CLOSE, $locale);
    }

    /**
     * Fetches reasons for sending an order message
     *
     * @param string $locale
     * @return ReasonCollection
     */
    public function getOrderMessageReasons($locale = null)
    {
        return $this->getTypeReasons(ReasonType::ORDER_MESSAGING, $locale);
    }
}
