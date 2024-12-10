<?php
namespace Mirakl\Core\Block\Adminhtml\Shipping\Type\Grid;

class Container extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mirakl_Core';
        $this->_controller = 'adminhtml_shipping_type';
        $this->_headerText = __('Shipping Method List');
        parent::_construct();
        $this->removeButton('add');
        $this->addButton(
            'synchronize',
            [
                'label' => __('Synchronize Shipping Methods'),
                'class' => 'save primary',
                'onclick' => 'confirmSetLocation(\'' . __(
                        'Are you sure? This will synchronize all active Mirakl shipping methods in Magento.'
                    ) . '\', \'' . $this->getUrl('*/*/sync') . '\')'
            ]
        );
    }
}
