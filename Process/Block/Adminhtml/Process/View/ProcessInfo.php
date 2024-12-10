<?php
declare(strict_types=1);

namespace Mirakl\Process\Block\Adminhtml\Process\View;

class ProcessInfo extends AbstractView
{
    /**
     * {@inheritdoc}
     */
    protected function _construct(): void
    {
        parent::_construct();

        $process = $this->getProcess();

        if (!$process) {
            return;
        }

        $this->addButton('back', [
            'label'   => __('Back'),
            'onclick' => "setLocation('" . $this->getBackUrl() . "')",
            'class'   => 'back'
        ]);

        $this->addButton(
            'delete',
            [
                'label' => __('Delete'),
                'class' => 'primary',
                'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\', {data: {}})'
            ]
        );

        if ($process->canRun()) {
            $this->addButton('run', [
                'label'   => __('Run'),
                'onclick' => 'confirmSetLocation(\'' . __(
                    'Are you sure?'
                    ) . '\', \'' . $this->getRunUrl() . '\')',
            ]);
        } elseif ($process->canStop()) {
            $this->addButton('stop', [
                'label'   => __('Stop'),
                'onclick' => 'confirmSetLocation(\'' . __(
                    'Are you sure?'
                    ) . '\', \'' . $this->getStopUrl() . '\')',
            ]);
        }
    }

    /**
     * @return string
     */
    protected function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getProcess()->getId()]);
    }

    /**
     * @return string
     */
    public function getRunUrl(): string
    {
        return $this->getUrl('*/*/run', ['id' => $this->getProcess()->getId()]);
    }

    /**
     * @return string
     */
    public function getStopUrl(): string
    {
        return $this->getUrl('*/*/stop', ['id' => $this->getProcess()->getId()]);
    }

    /**
     * @return string
     */
    protected function getBackUrl(): string
    {
        if ($this->getProcess()->getParentId()) {
            return $this->getUrl('*/*/view', ['id' => $this->getProcess()->getParentId()]);
        }

        return $this->getUrl('*/*/');
    }
}
