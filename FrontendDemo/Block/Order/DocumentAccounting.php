<?php
namespace Mirakl\FrontendDemo\Block\Order;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Sales\Model\Order;
use Mirakl\Api\Helper\DocumentRequest as DocumentRequestApi;
use Mirakl\MMP\Common\Domain\Collection\SeekableCollection;
use Mirakl\MMP\Front\Domain\DocumentRequest\DocumentResponse;

class DocumentAccounting extends Template
{
    /**
     * @var DocumentRequestApi
     */
    protected $documentRequestApi;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var string
     */
    protected $_template = 'order/document_accounting.phtml';

    /**
     * @param TemplateContext    $context
     * @param DocumentRequestApi $documentRequestApi
     * @param Registry           $registry
     * @param FormKey            $formKey
     * @param array              $data
     */
    public function __construct(
        Template\Context $context,
        DocumentRequestApi $documentRequestApi,
        Registry $registry,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->documentRequestApi = $documentRequestApi;
        $this->registry = $registry;
        $this->formKey = $formKey;
    }

    /**
     * Retrieves accounting document list of current Mirakl order
     *
     * @return SeekableCollection
     */
    public function getOrderAccountingDocuments()
    {
        return $this->documentRequestApi->getOrderAccountingDocuments($this->getMiraklOrder());
    }

    /**
     * @param DocumentResponse $doc
     * @return string
     */
    public function getDownloadUrl(DocumentResponse $doc)
    {
        return $this->getUrl('*/order/downloadAccounting', [
            'order_id'  => $this->getOrder()->getId(),
            'remote_id' => $this->getMiraklOrder()->getId(),
            'doc_id'    => $doc->getId(),
            'form_key'  => $this->formKey->getFormKey(),
        ]);
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return \Mirakl\MMP\FrontOperator\Domain\Order
     */
    public function getMiraklOrder()
    {
        return $this->registry->registry('mirakl_order');
    }
}
