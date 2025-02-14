<?php

declare(strict_types=1);

namespace Mirakl\Connector\Block\Adminhtml\System\Config\Button;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
abstract class AbstractClearButton extends Field
{
    public const BUTTON_TEMPLATE = 'Mirakl_Connector::system/config/button.phtml';

    /**
     * @var string
     */
    protected $confirmLabel = 'Are you sure? This will clear all entries.';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param Context            $context
     * @param ResourceConnection $resource
     * @param array              $data
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @return string
     */
    abstract protected function getTableName();

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData([
            'button_label'  => __($originalData['button_label']),
            'button_url'    => $this->getUrl($originalData['button_url']),
            'button_class'  => 'scalable primary',
            'html_id'       => $element->getHtmlId(),
            'confirm_label' => __($this->confirmLabel),
        ]);

        $rowsCount = $this->getRowsCount();

        return $this->_toHtml() . __('(%1 item%2)', $rowsCount, $rowsCount > 1 ? 's' : '');
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE); // @phpstan-ignore-line
        }

        return $this;
    }

    /**
     * @return int
     */
    private function getRowsCount()
    {
        $count = 0;
        try {
            $select = $this->connection->select()
                ->from($this->resource->getTableName($this->getTableName()), 'COUNT(*)');
            $count = $this->connection->fetchOne($select);
        } catch (\Exception $e) {
            // Ignore exception
        }

        return $count;
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }
}
