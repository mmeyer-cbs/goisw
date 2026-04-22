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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Model\Config\Source;

class AutoApprovalOptions implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Const
     */
    const AUTO_APPROVE_ACC = 0;
    const NOT_AUTO_APPROVE_ACC = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Auto Approve Accounts'), 'value' => self::AUTO_APPROVE_ACC],
            ['label' => __('Not Auto Approve Accounts'), 'value' => self::NOT_AUTO_APPROVE_ACC]
        ];
    }
}
