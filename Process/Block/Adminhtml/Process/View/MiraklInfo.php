<?php
declare(strict_types=1);

namespace Mirakl\Process\Block\Adminhtml\Process\View;

class MiraklInfo extends AbstractView
{
    /**
     * {@inheritdoc}
     */
    protected function _construct(): void
    {
        parent::_construct();

        $process = $this->getProcess();

        if ($process && $process->canCheckMiraklStatus()) {
            $confirmText = $this->_escaper->escapeJs(__('Are you sure?'));
            $this->buttonList->add('check_mirakl_status', [
                'label'   => __('Check Mirakl Status'),
                'onclick' => "confirmSetLocation('$confirmText', '{$this->getCheckMiraklStatusUrl()}')",
            ]);
        }
    }

    /**
     * @return  string
     */
    public function getCheckMiraklStatusUrl(): string
    {
        return $this->getUrl('*/*/checkMiraklStatus', ['id' => $this->getProcess()->getId()]);
    }
}
