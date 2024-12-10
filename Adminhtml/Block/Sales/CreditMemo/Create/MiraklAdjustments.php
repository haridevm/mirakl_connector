<?php
namespace Mirakl\Adminhtml\Block\Sales\CreditMemo\Create;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Tax\Model\Config as TaxConfig;

class MiraklAdjustments extends Template
{
    /**
     * @var Creditmemo
     */
    protected $_source;

    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * @param   Template\Context    $context
     * @param   TaxConfig           $taxConfig
     * @param   array               $data
     */
    public function __construct(
        Template\Context $context,
        TaxConfig $taxConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->taxConfig = $taxConfig;
    }

    /**
     * @param   float   $value
     * @return  string
     */
    public function formatValue($value)
    {
        return sprintf('%.2F', $value);
    }

    /**
     * @return  $this
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $parent */
        $parent = $this->getParentBlock();

        $this->_source = $parent->getSource();

        $total = new DataObject([
            'code'       => 'mirakl_shipping',
            'block_name' => $this->getNameInLayout(),
        ]);

        $parent->addTotalBefore($total, 'adjustments');

        return $this;
    }

    /**
     * @return  float
     */
    public function getShippingAmount()
    {
        $source = $this->getSource();

        /** @var \Magento\Sales\Api\Data\CreditmemoExtension $extensionAttributes */
        $extensionAttributes = $source->getExtensionAttributes();

        if ($this->taxConfig->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            return $extensionAttributes->getMiraklBaseShippingInclTax();
        }

        return $extensionAttributes->getMiraklBaseShippingExclTax();
    }

    /**
     * @return string
     */
    public function getShippingLabel()
    {
        $source = $this->getSource();

        if ($this->taxConfig->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $label = __('Refund Marketplace Shipping (Incl. Tax)');
        } elseif ($this->taxConfig->displaySalesShippingBoth($source->getOrder()->getStoreId())) {
            $label = __('Refund Marketplace Shipping (Excl. Tax)');
        } else {
            $label = __('Refund Marketplace Shipping');
        }

        return $label;
    }

    /**
     * @return  Creditmemo
     */
    public function getSource()
    {
        return $this->_source;
    }
}