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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Api\Data;

/**
 * @api
 */
interface CreditInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const CUSTOMER_ID = 'customer_id';

    const CREDIT_LIMIT = 'credit_limit';

    const USED_CREDIT = 'used_credit';

    const BALANCE_CREDIT = 'available_credit';

    const CURRENCY_CODE = "currency_code";

    const ALLOW_EXCEED = "allow_exceed";

    const PAYMENT_DUE_DATE = 'payment_due_date';

    /**
     * Set Customer ID
     *
     * @param int $customerId
     * @return $this
     * @since 100.1.0
     */
    public function setCustomerId($customerId);

    /**
     * Set credit limit
     *
     * @param float $creditLimit
     * @return $this
     * @since 100.1.0
     */
    public function setCreditLimit($creditLimit);

    /**
     * Set payment due date
     *
     * @param int|null $paymentDueDate
     * @return $this
     * @since 100.1.0
     */
    public function setPaymentDueDate($paymentDueDate);

    /**
     * Set used credit
     *
     * @param float $usedCredit
     * @return $this
     * @since 100.1.0
     */
    public function setUsedCredit($usedCredit);

    /**
     * Set available credit
     *
     * @param float $availableCredit
     * @return $this
     * @since 100.1.0
     */
    public function setAvailableCredit($availableCredit);

    /**
     * Set currency code
     *
     * @param string $currencyCode
     * @return $this
     * @since 100.1.0
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Set allow exceed
     *
     * @param int $allowExceed
     * @return $this
     * @since 100.1.0
     */
    public function setAllowExceed($allowExceed);

    /**
     * Get credit limit
     *
     * @return float
     * @since 100.1.0
     */
    public function getCreditLimit();

    /**
     * Get payment due date
     *
     * @return float
     * @since 100.1.0
     */
    public function getPaymentDueDate();

    /**
     * Get used credit
     *
     * @return float
     * @since 100.1.0
     */
    public function getUsedCredit();

    /**
     * Get available credit
     *
     * @return float
     * @since 100.1.0
     */
    public function getAvailableCredit();

    /**
     * Get currency code
     *
     * @return float
     * @since 100.1.0
     */
    public function getCurrencyCode();

    /**
     * Get allow exceed
     *
     * @return int
     * @since 100.1.0
     */
    public function getAllowExceed();
}
