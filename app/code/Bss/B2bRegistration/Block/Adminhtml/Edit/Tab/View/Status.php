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
namespace Bss\B2bRegistration\Block\Adminhtml\Edit\Tab\View;

use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;

class Status extends \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo
{
    /**
     * Get customer status
     * @return string $value
     */
    public function getStatus()
    {
        $customerStatus = $this->getCustomer()->getCustomAttribute('b2b_activasion_status');
        if ($customerStatus) {
            $customerValue = $customerStatus->getValue();
            switch ($customerValue) {
                case CustomerAttribute::B2B_PENDING:
                    $customerValue = __("B2B Pending");
                    break;
                case CustomerAttribute::B2B_APPROVAL:
                    $customerValue = __("B2B Approval");
                    break;
                case CustomerAttribute::B2B_REJECT:
                    $customerValue = __("B2B Reject");
                    break;
                default:
                    $customerValue = __("Normal Account");
            }
        } else {
            $customerValue = __("Normal Account");
        }
        return $customerValue;
    }
}
