<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Block\Adminhtml\System\Config\Button\Import;

use Mirakl\Connector\Block\Adminhtml\System\Config\Button\AbstractButtons;

class ProductsFile extends AbstractButtons
{
    /**
     * @var array
     */
    protected $buttonsConfig = [
        [
            'label' => 'Import in Magento',
            'onclick' => "var el = jQuery('#mirakl_mcm_import_product_file'); "
                . "el.val() ? configForm.submit() : el.effect('shake', {distance:15}, 500);",
            'class' => 'scalable primary',
        ]
    ];
}
