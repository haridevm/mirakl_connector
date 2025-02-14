<?php

declare(strict_types=1);

namespace Mirakl\Event\Block\Adminhtml\Event\Grid\Column;

use Magento\Backend\Block\Widget\Grid\Column;
use Mirakl\Event\Model\Event;

class Status extends Column
{
    /**
     * Decorates column value
     *
     * @param string $value
     * @param Event  $row
     * @return string
     */
    public function decorate($value, $row)
    {
        return '<span class="' . $row->getStatusClass() . '"><span>' . __($value) . '</span></span>';
    }

    /**
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorate'];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach (Event::getStatuses() as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => __(ucwords(str_replace('_', ' ', $label))),
            ];
        }

        return $options;
    }
}
