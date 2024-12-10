<?php

declare(strict_types=1);

namespace Mirakl\Mci\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Core\Controller\Adminhtml\RedirectRefererTrait;
use Psr\Log\LoggerInterface;

abstract class Clear extends Action implements HttpGetActionInterface
{
    use RedirectRefererTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context         $context
     * @param LoggerInterface $logger
     */
    public function __construct(Context $context, LoggerInterface $logger)
    {
        parent::__construct($context);
        $this->logger = $logger;
    }
}
