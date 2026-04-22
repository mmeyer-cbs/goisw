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
namespace Bss\B2bRegistration\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Bss\B2bRegistration\Helper\Data;
use Magento\Customer\Model\EmailNotificationInterface;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;

class EmailNotification
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * EmailNotification constructor.
     * @param Data $helper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Customer\Model\EmailNotification $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param null $sendemailStoreId
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotification $subject,
        callable $proceed,
        CustomerInterface $customer,
        $type,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        $enable = $this->helper->isEnable();
        $isAutoApproval = $this->helper->isAutoApproval();
        $b2bAccount = $this->registry->registry('bss_b2b_account');
        if (($type == EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED ||
                $type == EmailNotificationInterface::NEW_ACCOUNT_EMAIL_CONFIRMED
            ) &&
            $enable &&
            !$isAutoApproval &&
            $b2bAccount
        ) {
            return false;
        } else {
            return $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
        }
    }
}
