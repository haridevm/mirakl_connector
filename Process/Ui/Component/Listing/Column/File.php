<?php

declare(strict_types=1);

namespace Mirakl\Process\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirakl\Core\Helper\Data as CoreHelper;
use Mirakl\Process\Helper\Config as ProcessConfig;
use Mirakl\Process\Helper\Data as ProcessHelper;

class File extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var ProcessConfig
     */
    private $processConfig;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param CoreHelper         $coreHelper
     * @param ProcessHelper      $processHelper
     * @param ProcessConfig      $processConfig
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CoreHelper $coreHelper,
        ProcessHelper $processHelper,
        ProcessConfig $processConfig,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->coreHelper = $coreHelper;
        $this->processHelper = $processHelper;
        $this->processConfig = $processConfig;

        // Display HTML in cells
        $data['config']['bodyTmpl'] = 'ui/grid/cells/html';

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                if ($row[$this->getName()]) {
                    $row[$this->getName()] = $this->decorate($row);
                }
            }
        }
        unset($row);

        return $dataSource;
    }

    /**
     * @param array $row
     * @return string
     */
    public function decorate(array $row): string
    {
        $isMirakl = strstr($this->getName(), 'mirakl') !== false;
        $file = $row[$this->getName()];
        $html = '';
        if ($fileSize = $this->getFileSizeFormatted($file, '&nbsp;')) {
            $html = sprintf(
                '<a href="%s">%s</a>&nbsp;(%s)',
                $this->getDownloadFileUrl($row['id'], $isMirakl),
                __('Download'),
                $fileSize
            );
            if ($this->canShowFile($file)) {
                $html .= sprintf(
                    '<br/> %s <a target="_blank" href="%s" title="%s">%s</a>',
                    __('or'),
                    $this->urlBuilder->getUrl('mirakl/process/showFile', ['id' => $row['id'], 'mirakl' => $isMirakl]),
                    __('Open in Browser'),
                    __('open in browser')
                );
            }
        }

        return $html;
    }

    /**
     * @param string      $file
     * @param string|null $separator
     * @return string|false
     */
    protected function getFileSizeFormatted(string $file, ?string $separator = ' ')
    {
        if ($fileSize = $this->processHelper->getFileSize($file)) {
            return $this->coreHelper->formatSize($fileSize, $separator);
        }

        return false;
    }

    /**
     * @param int  $processId
     * @param bool $isMirakl
     * @return string
     */
    protected function getDownloadFileUrl($processId, bool $isMirakl): string
    {
        return $this->urlBuilder->getUrl('mirakl/process/downloadFile', [
            'id' => $processId,
            'mirakl' => $isMirakl,
        ]);
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function canShowFile(string $file): bool
    {
        $fileSize = $this->processHelper->getFileSize($file);

        return $fileSize <= ($this->processConfig->getShowFileMaxSize() * 1024 * 1024); // less than 5 MB
    }
}
