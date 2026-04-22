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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Api;

use Bss\CompanyAccount\Exception\EmptyInputException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\ExpiredException;

/**
 * Interface SubUserManagementInterface
 *
 * @package Bss\CompanyAccount\Api
 */
interface SubUserManagementInterface
{
    const MAX_PASSWORD_LENGTH = 256;

    /**
     * Check if reset password link token is valid
     *
     * @param int $subId
     * @param string $token
     * @param null|int $websiteId
     * @return bool
     * @throws ExpiredException
     * @throws InputException
     * @throws LocalizedException
     */
    public function validateResetPasswordLinkToken($subId, $token, $websiteId = null);

    /**
     * Validate reset password token for sub-user
     *
     * @param int $subId
     * @param string $token
     * @param null|int $websiteId
     *
     * @return mixed
     */
    public function validateResetPasswordToken($subId, $token, $websiteId = null);

    /**
     * Reset sub-user password
     *
     * @param int|null $subId
     * @param string $token
     * @param string $password
     * @param int $websiteId
     *
     * @return void
     */
    public function resetPassword($subId, $token, $password, $websiteId);

    /**
     * Authenticate sub-user password
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @param string $password
     *
     * @return bool
     * @throws EmptyInputException
     * @throws \Exception
     */
    public function authenticate($subUser, $password);

    /**
     * Get sub-user by key
     *
     * @param string|int $value
     * @param string $key
     * @param int|null $websiteId
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    public function getSubUserBy($value, $key = 'id', $websiteId = null);

    /**
     * Get related customer
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @param int $websiteId
     * @return \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerBySubUser($subUser, $websiteId = null);
}
