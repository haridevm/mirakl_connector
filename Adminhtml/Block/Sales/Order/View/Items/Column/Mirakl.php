<?php
namespace Mirakl\Adminhtml\Block\Sales\Order\View\Items\Column;

class Mirakl extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * @return  float
     */
    public function getItemBaseShippingPriceExclTax()
    {
        return $this->getItem()->getMiraklBaseShippingExclTax();
    }

    /**
     * @return  float
     */
    public function getItemBaseShippingPriceInclTax()
    {
        return $this->getItem()->getMiraklBaseShippingInclTax();
    }

    /**
     * @return  float
     */
    public function getItemShippingPriceExclTax()
    {
        return $this->getItem()->getMiraklShippingExclTax();
    }

    /**
     * @return  float
     */
    public function getItemShippingPriceInclTax()
    {
        return $this->getItem()->getMiraklShippingInclTax();
    }
}