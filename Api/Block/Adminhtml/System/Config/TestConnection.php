<?php
declare(strict_types=1);

namespace Mirakl\Api\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Mirakl_Api::system/config/test-connection.phtml';

    /**
     * Remove element scope and render form element as HTML
     *
     * @inheritdoc
     */
    public function render(AbstractElement $element): string
    {
        $element->setData('scope');

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $this->addData([
            'button_label' => __($element->getOriginalData()['button_label']),
        ]);

        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->_urlBuilder->getUrl('mirakl_api/system_config/testConnection', [
            'form_key' => $this->getFormKey(),
        ]);
    }
}