<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirakl\Process\Model\Process;

class StatusLayout implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach (Process::getStatuses() as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => __(ucwords(str_replace('_', ' ', $label))),
            ];
        }

        return $options;
    }
}
