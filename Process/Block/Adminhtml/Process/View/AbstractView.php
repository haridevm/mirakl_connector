<?php

declare(strict_types=1);

namespace Mirakl\Process\Block\Adminhtml\Process\View;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\Repository as ProcessRepository;

abstract class AbstractView extends Container
{
    /**
     * @var ProcessRepository
     */
    protected $processRepository;

    /**
     * @param Context           $context
     * @param ProcessRepository $processRepository
     * @param array             $data
     */
    public function __construct(
        Context $context,
        ProcessRepository $processRepository,
        array $data = []
    ) {
        $this->processRepository = $processRepository;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct(): void
    {
        $this->_controller = 'adminhtml_process';

        parent::_construct();
    }

    /**
     * @return Process|null
     */
    public function getProcess(): ?Process
    {
        $id = $this->getRequest()->getParam('id');

        return $this->processRepository->get($id);
    }
}
