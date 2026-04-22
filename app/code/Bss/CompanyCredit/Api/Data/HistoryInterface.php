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
interface HistoryInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const CUSTOMER_ID = 'customer_id';

    const PO_NUMBER = 'po_number';

    const ORDER_ID = 'order_id';

    const TYPE = 'type';

    const CREDIT_CHANGE = 'credit_change';

    const AVAILABLE_CREDIT_CURRENT = 'available_credit_current';

    const COMMENT = 'comment';

    const ALLOW_EXCEED = 'allow_exceed';

    const UPDATED_TIME = 'updated_time';

    const CREATE_TIME = 'create_time';

    const CURRENCY_CODE = 'currency_code';

    const PAYMENT_STATUS = 'payment_status';

    const UNPAID_CREDIT = 'unpaid_credit';
    /**#@-*/

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     * @since 100.1.0
     */
    public function setCustomerId($customerId);

    /**
     * Set PO number
     *
     * @param string $pONumber
     * @return $this
     * @since 100.1.0
     */
    public function setPONumber($pONumber);

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return $this
     * @since 100.1.0
     */
    public function setOrderId($orderId);

    /**
     * Set type
     *
     * @param int $type
     * @return $this
     * @since 100.1.0
     */
    public function setType($type);

    /**
     * Set credit change
     *
     * @param float $creditChange
     * @return $this
     * @since 100.1.0
     */
    public function setCreditChange($creditChange);

    /**
     * Set available credit currency
     *
     * @param float $availableCreditCurrent
     * @return $this
     * @since 100.1.0
     */
    public function setAvailableCreditCurrent($availableCreditCurrent);

    /**
     * Set comment
     *
     * @param string $comment
     * @return $this
     * @since 100.1.0
     */
    public function setComment($comment);

    /**
     * Set allow exceed
     *
     * @param int $allowExceed
     * @return $this
     * @since 100.1.0
     */
    public function setAllowExceed($allowExceed);

    /**
     * Set currency code
     *
     * @param string $currencyCode
     * @return $this
     * @since 100.1.0
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Set payment status
     *
     * @param string|null $paymentStatus
     * @return $this
     * @since 100.1.0
     */
    public function setPaymentStatus($paymentStatus);

    /**
     * Set unpaid credit
     *
     * @param float|null $unpaidCredit
     * @return $this
     * @since 100.1.0
     */
    public function setUnpaidCredit($unpaidCredit);

    /**
     * Get PO number
     *
     * @return string
     * @since 100.1.0
     */
    public function getPONumber();

    /**
     * Get order id
     *
     * @return int
     * @since 100.1.0
     */
    public function getOrderId();

    /**
     * Get type
     *
     * @return string
     * @since 100.1.0
     */
    public function getType();

    /**
     * Get credit change
     *
     * @return float
     * @since 100.1.0
     */
    public function getCreditChange();

    /**
     * Get available credit current
     *
     * @return float
     * @since 100.1.0
     */
    public function getAvailableCreditCurrent();

    /**
     * Get comment
     *
     * @return string
     * @since 100.1.0
     */
    public function getComment();

    /**
     * Get allow exceed
     *
     * @return string
     * @since 100.1.0
     */
    public function getAllowExceed();

    /**
     * Get currency code
     *
     * @return string
     * @since 100.1.0
     */
    public function getCurrencyCode();

    /**
     * Get payment status
     *
     * @return string
     * @since 100.1.0
     */
    public function getPaymentStatus();

    /**
     * Get unpaid credit
     *
     * @return string
     * @since 100.1.0
     */
    public function getUnpaidCredit();
}
