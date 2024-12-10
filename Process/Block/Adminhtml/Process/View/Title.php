<?php

declare(strict_types=1);

namespace Mirakl\Process\Block\Adminhtml\Process\View;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\Repository as ProcessRepository;

class Title extends Html\Title
{
    /**
     * @var ProcessRepository
     */
    protected $processRepository;

    /**
     * @param Context              $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ProcessRepository    $processRepository
     * @param array                $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ProcessRepository $processRepository,
        array $data = []
    ) {
        parent::__construct($context, $scopeConfig, $data);
        $this->processRepository = $processRepository;
    }

    /**
     * @return string
     */
    public function getPageTitle(): string
    {
        $process = $this->getProcess();

        if (!$process) {
            return parent::getPageTitle();
        }

        return $this->getProcessLabel((int) $process->getId());
    }

    /**
     * @return Process|null
     */
    public function getProcess(): ?Process
    {
        $id = $this->getRequest()->getParam('id');

        return $this->processRepository->get($id);
    }

    /**
     * @param int $id
     * @return string
     */
    public function getProcessLabel(int $id): string
    {
        return __('Process #%1', $id)->__toString();
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getProcess() ? (int) $this->getProcess()->getParentId() : null;
    }
}
