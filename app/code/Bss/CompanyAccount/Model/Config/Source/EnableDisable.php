<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model\Config\Source;

/**
 * Class EnableDisable
 *
 * @package Bss\CompanyAccount\Model\Config\Source
 */
class EnableDisable implements \Magento\Framework\Option\ArrayInterface
{
    const ENABLE = 1;
    const DISABLE = 0;

    /**
     * Get enable/disable option
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Enable'),
                'value' => self::ENABLE
            ],
            [
                'label' => __('Disable'),
                'value' => self::DISABLE
            ]
        ];
    }
}
