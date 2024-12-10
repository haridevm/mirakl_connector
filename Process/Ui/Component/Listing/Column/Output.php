<?php
declare(strict_types=1);

namespace Mirakl\Process\Ui\Component\Listing\Column;

use Magento\Framework\Filter\FilterManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Output extends Column
{
    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterManager      $filterManager
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterManager $filterManager,
        array $components = [],
        array $data = []
    ) {
        $this->filterManager = $filterManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $value = (string) $row[$this->getName()];
                if (strlen($value)) {
                    $lines = array_slice(explode("\n", $value), 0, 6);
                    if (count($lines) === 6) {
                        $lines[5] = '...';
                    }
                    array_walk($lines, function (&$line) {
                        $line = $this->truncate($line);
                    });
                    $row[$this->getName()] = implode('<br/>', $lines);
                }
            }
            unset($row);
        }

        return $dataSource;
    }

    /**
     * @param string      $value
     * @param int|null    $length
     * @param string|null $etc
     * @param string|null $remainder
     * @param bool|null   $breakWords
     * @return string
     */
    private function truncate(
        string $value,
        ?int $length = 80,
        ?string $etc = '...',
        ?string &$remainder = '',
        ?bool $breakWords = true
    ): string {
        return $this->filterManager->truncate(
            strip_tags($value),
            ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $breakWords]
        );
    }
}
