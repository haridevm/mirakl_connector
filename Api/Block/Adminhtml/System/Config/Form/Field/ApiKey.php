<?php

declare(strict_types=1);

namespace Mirakl\Api\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ApiKey extends Field
{
    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $message = __('The API Front Key authentication is not recommended.<br>'
            . 'Please use <strong>Access Token</strong> or <strong>OAuth 2.0 Client</strong> authentication instead.');

        $html = <<<HTML
<tr>
    <td></td>
    <td>
        <div class="message message-notice notice">$message</div>
    </td>
    <td colspan="2"></td>
</tr>
HTML;

        $html .= parent::render($element);

        return $html;
    }
}
