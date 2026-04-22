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

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerData
 *
 * @package Bss\CompanyAccount\Plugin\Customer
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CustomerData
{
    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CustomerData constructor.
     *
     * @param Session $customerSession
     * @param PermissionsChecker $permissionsChecker
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $customerSession,
        PermissionsChecker $permissionsChecker,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->permissionsChecker = $permissionsChecker;
        $this->logger = $logger;
    }

    /**
     * After get customer session data
     *
     * Will change full name of customer to sub-user name whenever
     * account login in is sub-user account
     *
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param array $result
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();
        try {
            if ($subUser) {
                $result['fullname'] = $subUser->getSubName() . " [" . $result['fullname'] . "]";
                if (!$this->permissionsChecker->check(Permissions::PLACE_ORDER_WAITING)) {
                    $result['role'] = 'false';
                }
            }
        } catch (LocalizedException | NoSuchEntityException $e) {
            $this->logger->critical($e);
        }
        return $result;
    }
}
