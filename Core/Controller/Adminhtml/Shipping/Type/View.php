<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Shipping\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Mirakl\Core\Model\Shipping\TypeFactory as ShippingTypeFactory;
use Mirakl\Core\Model\ResourceModel\Shipping\Type as ShippingTypeResource;

class View extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Core::shipping_types';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ShippingTypeFactory
     */
    private $shippingTypeFactory;

    /**
     * @var ShippingTypeResource
     */
    private $shippingTypeResource;

    /**
     * @param Context              $context
     * @param Registry             $coreRegistry
     * @param ShippingTypeFactory  $shippingTypeFactory
     * @param ShippingTypeResource $shippingTypeResource
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ShippingTypeFactory $shippingTypeFactory,
        ShippingTypeResource $shippingTypeResource
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->shippingTypeFactory = $shippingTypeFactory;
        $this->shippingTypeResource = $shippingTypeResource;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $model = $this->shippingTypeFactory->create();
        $this->shippingTypeResource->load($model, $id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This shipping method no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $this->coreRegistry->register('mirakl_shipping_type', $model);
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mirakl_Core::shipping_types');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Method #%1', $model->getId()));
        $this->_view->renderLayout();

        return $this->_response;
    }
}
