<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\CustomerAttributes\Block\Form\Edit;

class GetAttributeValues
{
    /**
     * Get Attribute Values
     *
     * @param mixed $value
     * @return array|null
     */
    public function getAttributeValues($value): ?array
    {
        if ($value !== null && isset($value['dependents_data']) && $value['dependents_data'] !== 'null') {
            return [
                'value' =>$value
            ];
        }
        return null;
    }
}
