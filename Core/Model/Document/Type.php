<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\Document;

use Magento\Framework\Model\AbstractModel;

/**
 * @method $this  setLabel(string $label)
 * @method string getLabel()
 * @method $this  setType(string $type)
 * @method string getType()
 *
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Type extends AbstractModel
{
    public const DOCUMENT_TYPE_ID  = 'id'; // We define the id fieldname

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mirakl_document_type'; // parent value is 'core_abstract'

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'document_type'; // parent value is 'object'

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::DOCUMENT_TYPE_ID; // parent value is 'id'

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Mirakl\Core\Model\ResourceModel\Document\Type::class);
    }
}
