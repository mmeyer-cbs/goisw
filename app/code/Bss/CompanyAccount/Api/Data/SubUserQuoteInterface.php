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

/**
 * Interface SubUserQuoteInterface
 *
 * @package Bss\CompanyAccount\Api\Data
 */
interface SubUserQuoteInterface
{
    /**
     * Constants for keys of data array.
     */
    const ID = 'entity_id';
    const QUOTE_STATUS = 'quote_status';
    const ACTION_BY = 'action_by';
    const IS_BACK_QUOTE = 'is_back_quote';
    const QUOTE_ID = 'quote_id';
    const SUB_USER_ID = 'sub_id';
    const CUSTOMER_ID = 'customer_id';

    // Type back quote
    const BACK_QUOTE_BLANK = 0;
    const USER_BACK_QUOTE = 1;
    const ADMIN_BACK_QUOTE = 2;

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * Get sub-user id
     *
     * @return int
     */
    public function getSubId();

    /**
     * Set sub-user id
     *
     * @param int $id
     * @return void
     */
    public function setSubId($id);

    /**
     * Get action by
     *
     * @return mixed
     */
    public function getActionBy();

    /**
     * Set action by
     *
     * @param int $id
     * @return mixed
     */
    public function setActionBy($id);

    /**
     * Get order id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set order id
     *
     * @param int $id
     * @return mixed
     */
    public function setQuoteId($id);

    /**
     * Get quote status
     *
     * @return mixed
     */
    public function getQuoteStatus();

    /**
     * Set quote status
     *
     * @param string $status
     * @return mixed
     */
    public function setQuoteStatus($status);

    /**
     * Get quote status
     *
     * @return mixed
     */
    public function getIsBackQuote();

    /**
     * Set quote status
     *
     * @param bool $check
     * @return mixed
     */
    public function setIsBackQuote($check);
}
