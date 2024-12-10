<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\Shipping;

use Magento\Framework\Model\AbstractModel;

/**
 * @method string getCode()
 * @method $this  setCode(string $value)
 * @method string getDeliveryByOperator()
 * @method $this  setDeliveryByOperator(bool $deliveryByOperator)
 * @method int    getIsMandatoryTracking()
 * @method $this  setIsMandatoryTracking(bool $mandatoryTracking)
 * @method string getLabel()
 * @method $this  setLabel(string $label)
 * @method string getDescription()
 * @method $this  setDescription(string $description)
 *
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Type extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'core_shipping_type';

    /**
     * @var string
     */
    protected $_eventObject = 'shipping_type';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init(\Mirakl\Core\Model\ResourceModel\Shipping\Type::class);
        $this->setIdFieldName('id');
    }
}
