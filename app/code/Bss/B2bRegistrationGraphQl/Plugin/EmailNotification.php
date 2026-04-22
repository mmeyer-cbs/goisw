<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_B2bRegistrationGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\B2bRegistrationGraphQl\Plugin;

use Bss\B2bRegistration\Helper\Data;
use Magento\Customer\Model\Session;

class EmailNotification
{
    /**
     * @var \Bss\B2bRegistration\Helper\Data
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * Construct
     *
     * @param Data $moduleHelper
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     */
    public function __construct(
        \Bss\B2bRegistration\Helper\Data $moduleHelper,
        \Magento\Customer\Model\SessionFactory $customerSession
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * Check auto appravo -> send mail
     *
     * @param \Magento\Customer\Test\Unit\Model\EmailNotificationTest $emailNotification
     * @param callable $proceed
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @return mixed|void
     */
    public function aroundNewAccount($emailNotification, $proceed, \Magento\Customer\Model\Data\Customer $customer)
    {
        $storeId = $customer->getStoreId();
        if (!$this->moduleHelper->isAutoApproval()
            && $this->customerSession->create()->getB2bAccount()
        ) {
            return;
        } else {
            return $proceed($customer, 'registered', '', $storeId);
        }
    }
}
