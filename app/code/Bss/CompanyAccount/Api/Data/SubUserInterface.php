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
namespace Bss\CompanyAccount\Api\Data;

use Magento\Customer\Model\Customer;

/**
 * Interface SubUserInterface
 *
 * @package Bss\CompanyAccount\Api\Data
 * @method int getId()
 * @method array getData(string $key = null)
 */
interface SubUserInterface
{
    /**
     * Constants for keys of data array.
     */
    const ID = 'sub_id';
    const CUSTOMER_ID = 'customer_id';
    const NAME = 'sub_name';
    const EMAIL = 'sub_email';
    const PASSWORD = 'sub_password';
    const STATUS = 'sub_status';
    const TOKEN = 'token';
    const ROLE_ID = 'role_id';
    const CREATE_AT = 'created_at';
    const QUOTE_ID = 'quote_id';
    const PARENT_QUOTE_ID = 'parent_quote_id';
    const TOKEN_EXPIRES_AT = 'token_expires_at';
    const IS_SENT_MAIL = 'is_sent_email';

    // Relation
    const ROLE = "role";
    const CUSTOMER = "customer";

    /**
     * Get token expires time
     *
     * @return string
     */
    public function getTokenExpiresAt();

    /**
     * Set expires time
     *
     * @param string $date
     * @return void
     */
    public function setTokenExpiresAt($date);

    /**
     * Get quote id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set quote Id
     *
     * @param int $id
     * @return void
     */
    public function setQuoteId($id);

    /**
     * Get parent quote id
     *
     * @return int
     */
    public function getParentQuoteId();

    /**
     * Set parent quote id
     *
     * @param int $id
     * @return void
     */
    public function setParentQuoteId($id);

    /**
     * Get create time
     *
     * @return string
     */
    public function getCreateTime();

    /**
     * Set create time
     *
     * @param string $time
     * @return void
     */
    public function setCreateTime($time);

    /**
     * Get sub user id
     *
     * @return int
     */
    public function getSubId();

    /**
     * Set sub user id
     *
     * @param int $id
     * @return void
     */
    public function setSubId($id);

    /**
     * Get company customer related id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Associate company customer
     *
     * @param int $id
     * @return void
     */
    public function setCustomerId($id);

    /**
     * Get sub user name
     *
     * @return string
     */
    public function getSubName();

    /**
     * Set name for sub user
     *
     * @param string $name
     * @return void
     */
    public function setSubName($name);

    /**
     * Get sub user email
     *
     * @return string
     */
    public function getSubEmail();

    /**
     * Set email for sub user
     *
     * @param string $email
     * @return void
     */
    public function setSubEmail($email);

    /**
     * Get sub user hashed password
     *
     * @return string
     */
    public function getSubPassword();

    /**
     * Set sub user password
     *
     * @param string $password
     * @return void
     */
    public function setSubPassword($password);

    /**
     * Get status of sub user
     *
     * @return int
     */
    public function getSubStatus();

    /**
     * Set sub user status
     *
     * @param int $status
     * @return void
     */
    public function setSubStatus($status);

    /**
     * Get token
     *
     * @return string
     */
    public function getToken();

    /**
     * Set token
     *
     * @param string $token
     * @return void
     */
    public function setToken($token);

    /**
     * Get related role id
     *
     * @return int
     */
    public function getRoleId();

    /**
     * Associate role for sub user
     *
     * @param int $id
     * @return void
     */
    public function setRoleId($id);

    /**
     * Get is sent mail field value
     *
     * @return bool
     */
    public function getIsSentMail();

    /**
     * Set is sent mail value
     *
     * @param int $value
     * @return void
     */
    public function setIsSentMail($value);

    /**
     * Get role was assigned to sub-user. A sub-user has one role
     *
     * @return SubRoleInterface|null
     */
    public function role(): ?SubRoleInterface;

    /**
     * Get relation customer
     *
     * @return Customer|null
     */
    public function customer(): ?Customer;
}
