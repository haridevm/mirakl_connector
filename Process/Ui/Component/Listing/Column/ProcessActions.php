<?php
declare(strict_types=1);

namespace Mirakl\Process\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ProcessActions extends Column
{
    const PROCESS_URL_PATH_VIEW   = 'mirakl/process/view';
    const PROCESS_URL_PATH_DELETE = 'mirakl/process/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $viewUrl;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param Escaper            $escaper
     * @param array|null         $components
     * @param array|null         $data
     * @param string|null        $viewUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        ?array $components = [],
        ?array $data = [],
        ?string $viewUrl = self::PROCESS_URL_PATH_VIEW
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->viewUrl = $viewUrl;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['view'] = [
                        'href' => $this->urlBuilder->getUrl($this->viewUrl, ['id' => $item['id']]),
                        'label' => __('View'),
                    ];
                    $title = $this->escaper->escapeHtml(__('Process #%1', $item['id']));
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::PROCESS_URL_PATH_DELETE, ['id' => $item['id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $title),
                            'message' => __('Are you sure you want to delete a %1 record?', $title),
                        ],
                        'post' => true,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
