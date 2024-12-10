<?php

declare(strict_types=1);

namespace Mirakl\Process\Block\Adminhtml;

use Magento\Framework\Phrase;

class Button extends \Magento\Backend\Block\Widget\Button
{
    /**
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __($this->getData('label'));
    }

    /**
     * @inheritdoc
     */
    public function getOnClick(): string
    {

        if ($onclick = $this->getData('on_click') ?: $this->getData('onclick')) {
            return $onclick;
        }

        $url = $this->getUrl($this->getData('url'));

        if (!$confirm = $this->getData('confirm')) {
            return "setLocation('$url')";
        }

        $confirm = $this->_escaper->escapeJs(__($confirm));

        return "confirmSetLocation('$confirm', '$url')";
    }
}
