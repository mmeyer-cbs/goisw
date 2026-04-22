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

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\ResourceModel\Customer;

/**
 * Class AccountManagement
 *
 * @package Bss\CompanyAccount\Plugin\Customer\AccountManagement
 */
class AccountManagement
{
    /**
     * @var Customer
     */
    private $customerResource;

    /**
     * @var Data
     */
    private $helper;

    /**
     * AccountManagement constructor.
     *
     * @param Data $helper
     * @param Customer $customerResource
     */
    public function __construct(
        Data $helper,
        Customer $customerResource
    ) {
        $this->customerResource = $customerResource;
        $this->helper = $helper;
    }

    /**
     * Validate with sub-user email
     *
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param callable $proceed
     * @param string $customerEmail
     * @param null|int $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsEmailAvailable(
        \Magento\Customer\Model\AccountManagement $subject,
        callable $proceed,
        $customerEmail,
        $websiteId = null
    ) {
        $result = $proceed($customerEmail, $websiteId);
        if ($this->helper->isEnable()) {
            if (!$websiteId) {
                $websiteId = $this->helper->getWebsiteId();
            }
            $customerEmailResult = $this->customerResource->validateUniqueCustomer($customerEmail, $websiteId);
            if ($customerEmailResult) {
                return false;
            }
            return $result;
        }
        return $result;
    }
}
