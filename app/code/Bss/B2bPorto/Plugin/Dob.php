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
 * @package    Bss_B2bPorto
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bPorto\Plugin;

use Bss\B2bRegistration\Plugin\Dob as B2bRegistrationDob;

/**
 * Class Dob
 *
 * @package Bss\B2bPorto\Plugin
 */
class Dob extends B2bRegistrationDob
{
    /**
     * Get Extra Params
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHtmlExtraParams($date)
    {
        $extraParams = [
            "'validate-date':{'dateFormat': 'MM\dd\YYYY'}"
        ];

        if ($this->isRequired()) {
            $extraParams[] = 'required:true';
        }

        $extraParams = implode(', ', $extraParams);

        return 'data-validate="{' . $extraParams . '}"';
    }
}
