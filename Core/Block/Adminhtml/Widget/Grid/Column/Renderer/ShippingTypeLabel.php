<?php

declare(strict_types=1);

namespace Mirakl\Core\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;

class ShippingTypeLabel extends AbstractRenderer
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @param Context $context
     * @param Json    $json
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->json = $json;
    }

    /**
     * @inheritdoc
     */
    public function render(DataObject $row)
    {
        $value = $this->_getValue($row);
        if (empty($value)) {
            return '';
        }

        $labelsByLocale = $this->json->unserialize($value);

        $output = '';
        foreach ($labelsByLocale as $locale => $label) {
            if (!empty($label)) {
                $output .= sprintf(
                    '<tr><td>%s :</td><td>%s</td></tr>',
                    $locale,
                    $label
                );
            }
        }

        return $output
            ? '<table class="mirakl-shipping-types-table mirakl-shipping-type-label">' . $output . '</table>'
            : '';
    }
}
