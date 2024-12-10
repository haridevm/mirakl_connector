<?php

declare(strict_types=1);

namespace Mirakl\Process\Block\Adminhtml\Process\View;

use Magento\Backend\Block\Widget\Context;
use Mirakl\Process\Model\Repository as ProcessRepository;
use Mirakl\Process\Model\Output\Formatter;

class ProcessOutput extends AbstractView
{
    /**
     * @var Formatter\Factory
     */
    private $formatterFactory;

    /**
     * @param Context           $context
     * @param ProcessRepository $processRepository
     * @param Formatter\Factory $formatterFactory
     * @param array             $data
     */
    public function __construct(
        Context $context,
        ProcessRepository $processRepository,
        Formatter\Factory $formatterFactory,
        array $data = []
    ) {
        parent::__construct($context, $processRepository, $data);
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @param int|null $limit
     * @return array
     */
    public function getOutput(?int $limit = 1000): array
    {
        $output = explode("\n", $this->getProcess()->getOutput());
        $result = current(array_chunk($output, $limit));

        if (count($output) > count($result)) {
            $result[] = __('(truncated to %1 lines)', $limit);
        }

        return $result;
    }

    /**
     * @param string $str
     * @return string
     */
    public function format(string $str): string
    {
        return $this->formatterFactory->create('html')->format($str);
    }
}
