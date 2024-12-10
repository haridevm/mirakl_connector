<?php
declare(strict_types=1);

namespace Mirakl\Process\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirakl\Core\Helper\Data as Helper;

class Duration extends Column
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Helper             $helper
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Helper $helper,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
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
                    $row[$this->getName()] = $this->helper->formatDuration($row[$this->getName()]);
                }
            }
        }
        unset($row);

        return $dataSource;
    }
}
