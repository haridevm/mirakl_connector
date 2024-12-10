<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Output;

use Mirakl\Core\Helper\Data as CoreHelper;
use Mirakl\Process\Model\Process;
use Psr\Log\LoggerInterface;

abstract class AbstractOutput implements OutputInterface
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Formatter\Factory
     */
    protected $formatterFactory;

    /**
     * @var Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * @param CoreHelper        $coreHelper
     * @param Process           $process
     * @param LoggerInterface   $logger
     * @param Formatter\Factory $formatterFactory
     */
    public function __construct(
        CoreHelper $coreHelper,
        Process $process,
        LoggerInterface $logger,
        Formatter\Factory $formatterFactory
    ) {
        $this->coreHelper = $coreHelper;
        $this->process = $process;
        $this->logger = $logger;
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @inheritdoc
     */
    abstract public function display($str): self;

    /**
     * @inheritdoc
     */
    public function close(): self
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        $type = get_class($this);
        $backslashPos = strrpos($type, '\\');

        if (false !== $backslashPos) {
            $type = substr($type, $backslashPos + 1);
        }

        return strtolower($type);
    }

    /**
     * @return Formatter\FormatterInterface
     */
    protected function getFormatter(): Formatter\FormatterInterface
    {
        if (!$this->formatter) {
            $this->formatter = $this->formatterFactory->create('no_tags');
        }

        return $this->formatter;
    }

    /**
     * @param \Magento\Framework\Phrase|string $str
     * @return string
     */
    protected function format($str): string
    {
        return $this->getFormatter()->format((string) $str);
    }
}
