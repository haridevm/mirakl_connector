require([
    'jquery'
], function ($) {
    // If synchronous offers import (API OF51) is enabled in config,
    // asynchronous import (API OF52-OF53-OF54) must be disabled automatically and vice versa.
    // But we have to still be able to disable both.

    let offersImportEnableSelect = $('#mirakl_sync_offers_enable');
    let offersImportAsyncEnableSelect = $('#mirakl_sync_offers_async_enable');

    offersImportEnableSelect.on('change', function () {
        if (this.value === '1') {
            offersImportAsyncEnableSelect.val('0');
        }
    });

    offersImportAsyncEnableSelect.on('change', function () {
        if (this.value === '1') {
            offersImportEnableSelect.val('0');
        }
    });

    // If synchronous product import (API CM51) is enabled in config,
    // asynchronous product import (API CM52-CM53-CM54) must be disabled automatically and vice versa.
    // But we have to still be able to disable both.

    let mcmEnableSelect = $('#mirakl_mcm_import_product_enable_mcm');
    let mcmAsyncEnableSelect = $('#mirakl_mcm_import_product_async_enable_mcm');

    mcmEnableSelect.on('change', function () {
        if (this.value === '1') {
            mcmAsyncEnableSelect.val('0');

            // Hide corresponding configs
            let configElements = [
                '#row_mirakl_mcm_import_product_async_enable_product_import',
                '#row_mirakl_mcm_import_product_async_file',
                '#row_mirakl_mcm_import_product_async_button_products',
                '#row_mirakl_mcm_import_product_async_default_visibility',
                '#row_mirakl_mcm_import_product_async_default_tax_class',
                '#row_mirakl_mcm_import_product_async_auto_enable_product',
            ];

            $.each(configElements, function (index, element) {
                    $(element).hide();
                }
            );
        }
    });

    mcmAsyncEnableSelect.on('change', function () {
        if (this.value === '1') {
            mcmEnableSelect.val('0');

            // Hide corresponding configs
            let configElements = [
                '#row_mirakl_mcm_import_product_mode',
                '#row_mirakl_mcm_import_product_enable_product_import',
                '#row_mirakl_mcm_import_product_file',
                '#row_mirakl_mcm_import_product_button_products',
                '#row_mirakl_mcm_import_product_default_visibility',
                '#row_mirakl_mcm_import_product_default_tax_class',
                '#row_mirakl_mcm_import_product_auto_enable_product',
            ];

            $.each(configElements, function (index, element) {
                    $(element).hide();
                }
            );
        }
    });
});