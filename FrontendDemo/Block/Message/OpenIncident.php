<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Message;

use Magento\Sales\Model\Order;
use Mirakl\MMP\FrontOperator\Domain\Collection\Reason\ReasonCollection;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;
use Mirakl\MMP\FrontOperator\Domain\Reason;

/**
 * @method string getMiraklOrderLineId()
 * @method $this  setMiraklOrderLineId(string $miraklOrderLineId)
 */
class OpenIncident extends AbstractForm
{
    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_formTitle = 'Open Incident';

    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setAsModal(true);
    }

    /**
     * @inheritdoc
     */
    public function getButtonClass()
    {
        return 'action primary';
    }

    /**
     * @inheritdoc
     */
    public function getFormAction()
    {
        return $this->getUrl('marketplace/order/postIncident', [
            'order_id'      => $this->getOrder()->getId(),
            'order_line_id' => $this->getMiraklOrderLineId(),
            'remote_id'     => $this->getMiraklOrder()->getId()
        ]);
    }

    /**
     * @return ReasonCollection
     */
    public function getReasons()
    {
        $locale = $this->coreConfig->getLocale();

        return $this->reasonApi->getOpenIncidentReasons($locale);
    }

    /**
     * @inheritdoc
     */
    public function getReasonValue(Reason $reason)
    {
        return $reason->getCode();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @return MiraklOrder
     */
    public function getMiraklOrder()
    {
        return $this->coreRegistry->registry('mirakl_order');
    }

    /**
     * @return bool
     */
    public function withFile()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isReasonSelected(Reason $reason)
    {
        return ($this->getPostMessage($this->getFormField('subject')) == $reason->getCode());
    }
}
