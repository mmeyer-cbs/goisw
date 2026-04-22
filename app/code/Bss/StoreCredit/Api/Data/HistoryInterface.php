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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Api\Data;

/**
 * @api
 */
interface HistoryInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const HISTORY_ID = "history_id";

    const CUSTOMER_ID = 'customer_id';

    const CREDITMEMO_ID = 'creditmemo_id';

    const ORDER_ID = 'order_id';

    const WEBSITE_ID = 'website_id';

    const TYPE = 'type';

    const CHANGE_AMOUNT = 'change_amount';

    const BALANCE_AMOUNT = 'balance_amount';

    const COMMENT_CONTENT = 'comment_content';

    const IS_NOTIFIED = 'is_notified';

    const CREATED_TIME = 'created_time';

    const UPDATED_TIME = 'updated_time';

    const CURRENCY_CODE = 'currency_code';

    const CREDIT_CURRENCY_CODE = 'credit_currency_code';

    const CHANGE_AMOUNT_STORE_VIEW = 'change_amount_store_view';
    /**#@-*/

    /**
     * @param int $customerId
     * @return $this
     * @since 100.1.0
     */
    public function setCustomerId($customerId);

    /**
     * @param int $creditmemoId
     * @return $this
     * @since 100.1.0
     */
    public function setCreditmemoId($creditmemoId);

    /**
     * @param int $orderId
     * @return $this
     * @since 100.1.0
     */
    public function setOrderId($orderId);

    /**
     * @param int $websiteId
     * @return $this
     * @since 100.1.0
     */
    public function setWebsiteId($websiteId);

    /**
     * @param string $type
     * @return $this
     * @since 100.1.0
     */
    public function setType($type);

    /**
     * @param float $amount
     * @return $this
     * @since 100.1.0
     */
    public function setChangeAmount($amount);

    /**
     * @param float $amount
     * @return $this
     * @since 100.1.0
     */
    public function setBalanceAmount($amount);

    /**
     * @param string $comment
     * @return $this
     * @since 100.1.0
     */
    public function setCommentContent($comment);

    /**
     * @param bool $isNotified
     * @return $this
     * @since 100.1.0
     */
    public function setIsNotified($isNotified);

    /**
     * @return int
     * @since 100.1.0
     */
    public function getHistoryId();

    /**
     * @return int
     * @since 100.1.0
     */
    public function getCreditmemoId();

    /**
     * @return int
     * @since 100.1.0
     */
    public function getOrderId();

    /**
     * @return string
     * @since 100.1.0
     */
    public function getType();

    /**
     * @return float
     * @since 100.1.0
     */
    public function getChangeAmount();

    /**
     * @return float
     * @since 100.1.0
     */
    public function getBalanceAmount();

    /**
     * @return string
     * @since 100.1.0
     */
    public function getCommentContent();

    /**
     * @return string
     * @since 100.1.0
     */
    public function getCreatedTime();

    /**
     * @return string
     * @since 100.1.0
     */
    public function getUpdatedTime();

    /**
     * @return int
     * @since 100.1.0
     */
    public function getCustomerId();

    /**
     * @return int
     * @since 100.1.0
     */
    public function getWebsiteId();

    /**
     * Set currency code
     *
     * @return string|null
     * @param string $currencyCode
     * @since 100.1.0
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Get currency code
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getCurrencyCode();

    /**
     * Set credit currency code
     *
     * @return string|null
     * @param string $creditCurrencyCode
     * @since 100.1.0
     */
    public function setCreditCurrencyCode($creditCurrencyCode);

    /**
     * Get credit currency code
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getCreditCurrencyCode();

    /**
     * Set change amount in store view
     *
     * @param float $amount
     * @return $this
     * @since 100.1.0
     */
    public function setChangeAmountStoreView($amount);

    /**
     * Get change amount in store view
     *
     * @return float
     * @since 100.1.0
     */
    public function getChangeAmountStoreView();

}
