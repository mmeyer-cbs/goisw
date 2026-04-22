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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Model\Config\Source;

class DisplayBackendCustomerDetail
{
    public const ALL_ACCOUNT = 0;
    public const NORMAL_ACCOUNTS = 1;
    public const B2B_ACCOUNTS = 2;

    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ALL_ACCOUNT, 'label' => __('All accounts')],
            ['value' => self::NORMAL_ACCOUNTS, 'label' => __('Normal accounts')],
            ['value' => self::B2B_ACCOUNTS, 'label' => __('B2b accounts')]
        ];
    }
}
