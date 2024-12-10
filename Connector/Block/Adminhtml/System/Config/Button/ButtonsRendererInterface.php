<?php

declare(strict_types=1);

namespace Mirakl\Connector\Block\Adminhtml\System\Config\Button;

interface ButtonsRendererInterface
{
    /**
     * @return string
     */
    public function getButtonsHtml();

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisabled();
}
