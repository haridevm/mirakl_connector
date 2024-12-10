<?php
namespace Mirakl\Process\Block\Adminhtml\Process\View;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing;
use Mirakl\Process\Model\Repository as ProcessRepository;

class ChildList extends AbstractView
{
    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var Listing|null
     */
    protected $processListing;

    /**
     * @param Context            $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ProcessRepository  $processRepository
     * @param array              $data
     */
    public function __construct(
        Context $context,
        UiComponentFactory $uiComponentFactory,
        ProcessRepository $processRepository,
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        parent::__construct($context, $processRepository, $data);
    }

    /**
     * {@inheirtdoc}
     */
    protected function _toHtml(): string
    {
        return $this->getNbChildren() ? parent::_toHtml() : '';
    }

    /**
     * @return Listing
     * @throws LocalizedException
     */
    protected function getUIProcessListing(): Listing
    {
        if ($this->processListing == null) {
            $listing = $this->uiComponentFactory->create('mirakl_process_listing');
            if (!$listing instanceof Listing) {
                throw new ConfigurationMismatchException(__('UI component instance in incorrect type'));
            }
            $this->processListing = $listing;
        }

        return $this->processListing;
    }

    /**
     * @return int|null
     */
    public function getNbChildren(): ?int
    {
        try {
            $data = $this->getUIProcessListing()->getDataSourceData();
        } catch (LocalizedException $e) {
            $this->_logger->warning($e->getMessage());
            $data = [];
        }

        return $data['data']['totalRecords'] ?? null;
    }
}
