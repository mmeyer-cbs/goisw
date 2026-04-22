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

class CustomerAttribute extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    const NORMAL_ACCOUNT = 0;
    const B2B_PENDING = 1;
    const B2B_APPROVAL = 2;
    const B2B_REJECT = 3;

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = [];
        $options[] = [
                'label' => __('Normal Account'),
                'value' => self::NORMAL_ACCOUNT,
            ];
        $options[] = [
                'label' => __('B2B Pending'),
                'value' => self::B2B_PENDING,
            ];
        $options[] = [
                'label' => __('B2B Approval'),
                'value' => self::B2B_APPROVAL,
            ];
        $options[] = [
                'label' => __('B2B Reject'),
                'value' => self::B2B_REJECT,
            ];
        
        return $options;
    }
}
