<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Core\Controller\Adminhtml\RedirectRefererTrait;
use Mirakl\Mcm\Helper\Config as McmConfig;

abstract class AbstractController extends Action implements HttpGetActionInterface
{
    use RedirectRefererTrait;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Mcm::sync';

    /**
     * @var ConnectorConfig
     */
    protected $connectorConfig;

    /**
     * @var McmConfig
     */
    protected $mcmConfig;

    /**
     * @param Context         $context
     * @param ConnectorConfig $connectorConfig
     * @param McmConfig       $mcmConfig
     */
    public function __construct(
        Context $context,
        ConnectorConfig $connectorConfig,
        McmConfig $mcmConfig
    ) {
        parent::__construct($context);
        $this->connectorConfig = $connectorConfig;
        $this->mcmConfig = $mcmConfig;
    }
}
