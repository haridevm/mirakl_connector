<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Document;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirakl\Core\Model\ResourceModel\Document\TypeFactory as DocumentTypeResourceFactory;

/**
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
abstract class Type extends Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Core::document_types';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var DocumentTypeResourceFactory
     */
    protected $_documentTypeResourceFactory;

    /**
     * @param Context                     $context
     * @param Registry                    $coreRegistry
     * @param DocumentTypeResourceFactory $documentTypeResourceFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DocumentTypeResourceFactory $documentTypeResourceFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_documentTypeResourceFactory = $documentTypeResourceFactory;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mirakl_Core::document_types')
            ->_addBreadcrumb(__('Mirakl'), __('Mirakl'));

        return $this;
    }
}
