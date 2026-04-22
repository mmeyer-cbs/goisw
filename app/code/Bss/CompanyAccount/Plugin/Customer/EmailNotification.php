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
namespace Bss\CompanyAccount\Plugin\Customer;

/**
 * Class EmailNotification
 *
 * @package Bss\CompanyAccount\Plugin\customer
 */
class EmailNotification
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * EmailNotification constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Before send changed mail notification
     *
     * @param \Magento\Customer\Model\EmailNotification $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $savedCustomer
     * @param string $origCustomerEmail
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCredentialsChanged(
        \Magento\Customer\Model\EmailNotification $subject,
        $savedCustomer,
        $origCustomerEmail
    ) {
        $alreadyExistsEmail = $this->registry->registry('already_exists_email');
        if ($alreadyExistsEmail) {
            $savedCustomer->setEmail('no_send_mail');
            $origCustomerEmail = 'no_send_mail';
        }

        return [$savedCustomer, $origCustomerEmail];
    }
}
