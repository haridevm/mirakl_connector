<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\Type;

/**
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
trait TypeTrait
{
    /**
     * @param array $attrParams
     * @return array
     */
    protected function _customizeAttributeParams(array $attrParams): array
    {
        if (!empty($attrParams['options'])) {
            $attrParams['options'] = array_combine($attrParams['options'], $attrParams['options']);
        }

        return $attrParams;
    }
}
