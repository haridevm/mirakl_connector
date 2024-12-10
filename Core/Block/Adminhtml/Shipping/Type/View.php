<?php

declare(strict_types=1);

namespace Mirakl\Core\Block\Adminhtml\Shipping\Type;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Mirakl\Core\Model\Shipping\Type as ShippingType;

class View extends Container
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Context  $context
     * @param Registry $coreRegistry
     * @param Json     $json
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Json $json
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->json = $json;
    }

    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        $this->_mode = false;

        parent::_construct();

        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('delete');
    }

    /**
     * @return ShippingType
     */
    public function getShippingType()
    {
        return $this->coreRegistry->registry('mirakl_shipping_type');
    }

    /**
     * @param string       $key
     * @param ShippingType $shippingType
     * @return string
     */
    public function formatShippingTypeField(string $key, ShippingType $shippingType)
    {
        $method = 'get' . ucfirst($key);
        $value = $shippingType->$method();
        if (empty($value)) {
            return '';
        }

        $valuesByLocale = $this->json->unserialize($value);

        $output = '';
        foreach ($valuesByLocale as $locale => $valueByLocale) {
            if (!empty($valueByLocale)) {
                $output .= sprintf(
                    '<tr><td>%s :</td><td>%s</td></tr>',
                    $locale,
                    $valueByLocale
                );
            }
        }

        $html = '<table class="mirakl-shipping-types-table mirakl-shipping-type-%s">%s</table>';

        return $output ? sprintf($html, $key, $output) : '';
    }
}
