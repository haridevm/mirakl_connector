<?php

declare(strict_types=1);

namespace Mirakl\Connector\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Connector\Helper\Config;
use Mirakl\Core\Controller\Adminhtml\RedirectRefererTrait;

class IgnoreNotification extends Action implements HttpGetActionInterface
{
    use RedirectRefererTrait;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Connector::offers';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Context $context
     * @param Config  $config
     */
    public function __construct(
        Context $context,
        Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->config->setValue(Config::XML_PATH_IGNORE_OFFERS_NEW_IMPORT_NOTIFICATION, 1);
        $this->config->resetConfig();

        return $this->redirectReferer();
    }
}
