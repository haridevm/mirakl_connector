<?php
namespace Mirakl\FrontendDemo\Block\Order;

use Magento\Sales\Model\Order;

class Totals extends \Mirakl\Connector\Block\Sales\Order\Totals
{
    /**
     * {@inheritdoc}
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $parent */
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        if ($source instanceof Order && $source->getMiraklSent()) {
            return $this;
        }

        return parent::initTotals();
    }
}
