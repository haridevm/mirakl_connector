<?php
namespace Mirakl\Core\Controller\Adminhtml\Shipping\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirakl\Api\Helper\Config as ApiConfig;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Core\Controller\Adminhtml\RedirectRefererTrait;
use Mirakl\Core\Model\Shipping\Type\Synchronizer;
use Psr\Log\LoggerInterface;

class Sync extends Action
{
    use RedirectRefererTrait;

    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mirakl_Config::sync';

    /**
     * @var ApiConfig
     */
    private $apiConfig;

    /**
     * @var ConnectorConfig
     */
    private $connectorConfig;

    /**
     * @var Synchronizer
     */
    private $shippingTypeSynchronizer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context         $context
     * @param ApiConfig       $apiConfig
     * @param ConnectorConfig $connectorConfig
     * @param Synchronizer    $shippingTypeSynchronizer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ApiConfig $apiConfig,
        ConnectorConfig $connectorConfig,
        Synchronizer  $shippingTypeSynchronizer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->apiConfig = $apiConfig;
        $this->connectorConfig = $connectorConfig;
        $this->shippingTypeSynchronizer = $shippingTypeSynchronizer;
        $this->logger = $logger;
    }

    /**
     * Synchronize Mirakl shipping methods into Magento
     */
    public function execute()
    {
        try {
            if (!$this->checkConnectorEnabled()) {
                return $this->redirectReferer();
            }

            $this->shippingTypeSynchronizer->synchronize();
            $this->messageManager->addSuccessMessage(__('Shipping methods were synchronized successfully.'));

        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while synchronizing shipping methods: %1', $e->getMessage())
            );
        }

        return $this->redirectReferer();
    }

    /**
     * Will redirect with an error if Mirakl Connector is disabled in config
     *
     * @return  bool
     */
    private function checkConnectorEnabled()
    {
        if (!$this->apiConfig->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Mirakl Connector is currently disabled.'));
            return false;
        }

        return true;
    }
}
