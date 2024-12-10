<?php

declare(strict_types=1);

namespace Mirakl\Api\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class OAuth2 extends Field
{
    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $message = __('Make sure the refresh token <strong>cron job</strong> is properly configured.');

        $html = <<<HTML
<tr>
    <td></td>
    <td>
        <div class="message message-warning warning">$message</div>
    </td>
    <td colspan="2"></td>
</tr>
HTML;

        $html .= parent::render($element);

        return $html;
    }
}
