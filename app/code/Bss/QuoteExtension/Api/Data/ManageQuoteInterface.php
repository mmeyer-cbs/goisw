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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Api\Data;

/**
 * @api
 */
interface ManageQuoteInterface
{
    /**#@+
     * Constants defined for keys of data array
     */

    const ENTITY_ID = 'entity_id';

    const QUOTE_ID = 'quote_id';

    const BACKEND_QUOTE_ID = 'backend_quote_id';

    const TARGET_QUOTE = "target_quote";

    const INCREMENT_ID = "increment_id";

    const STORE_ID = "store_id";

    const CUSTOMER_ID = "customer_id";

    const STATUS = "status";

    const TOKEN = "token";

    const EMAIL = "email";

    const EMAIL_SENT = "email_sent";

    const UPDATED_AT = "updated_at";

    const CREATED_AT = "created_at";

    const EXPIRY = "expiry";

    const EXPIRY_EMAIL_SENT = "expiry_email_sent";

    const VERSION = "version";

    const OLD_QUOTE = "old_quote";

    const IS_ADMIN_SUBMTITED = "is_admin_submitted";

    const MOVE_CHECKOUT = "move_checkout";

    const CUSTOMER_NAME = "customer_name";

    const CUSTOMER_IS_GUEST = "customer_is_guest";

    /**
     * Set entity id
     * @param int|null $entitId
     *
     * @return $this
     */
    public function setEntityId($entitId);

    /**
     * Get entity id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set Quote ID
     *
     * @param int|null $quoteId
     * @return $this
     * @since 100.1.0
     */
    public function setQuoteId($quoteId = null);

    /**
     * Get Quote ID
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getQuoteId();

    /**
     * Get Backend Quote ID
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getBackendQuoteId();

    /**
     * Set Backend Quote ID
     *
     * @param int|null $backendQuoteId
     * @return $this
     * @since 100.1.0
     */
    public function setBackendQuoteId($backendQuoteId = null);


    /**
     * Get Target Quote ID
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getTargetQuote();


    /**
     * Set Backend Quote ID
     *
     * @param int|null $targetQuote
     * @return $this
     * @since 100.1.0
     */
    public function setTargetQuote($targetQuote = null);


    /**
     * Get Increment ID
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getIncrementId();


    /**
     * Set Increment ID
     *
     * @param int|null $incrementId
     * @return $this
     * @since 100.1.0
     */
    public function setIncrementId($incrementId = null);


    /**
     * Get Store ID
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getStoreId();


    /**
     * Set Store ID
     *
     * @param int|null $storeId
     * @return $this
     * @since 100.1.0
     */
    public function setStoreId($storeId = null);


    /**
     * Get Customer ID
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getCustomerId();


    /**
     * Set Customer ID
     *
     * @param int|null $customerId
     * @return $this
     * @since 100.1.0
     */
    public function setCustomerId($customerId = null);


    /**
     * Get Status
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getStatus();


    /**
     * Set Status
     *
     * @param string|null $status
     * @return $this
     * @since 100.1.0
     */
    public function setStatus($status = null);


    /**
     * Get Token
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getToken();


    /**
     * Set Status
     *
     * @param string|null $token
     * @return $this
     * @since 100.1.0
     */
    public function setToken($token = null);


    /**
     * Get Email
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getEmail();


    /**
     * Set Email
     *
     * @param string|null $email
     * @return $this
     * @since 100.1.0
     */
    public function setEmail($email = null);


    /**
     * Get EmailSent:Check sent email
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getEmailSent();

    /**
     * Set EmailSent
     *
     * @param int|null $emailSent
     * @return $this
     * @since 100.1.0
     */
    public function setEmailSent($emailSent = null);


    /**
     * Get Updated At
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string|null $updatedAt
     * @return $this
     * @since 100.1.0
     */
    public function setUpdatedAt($updatedAt = null);

    /**
     * Get Created At
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getCreatedAt();

    /**
     * Set Updated At
     *
     * @param string|null $createdAt
     * @return $this
     * @since 100.1.0
     */
    public function setCreatedAt($createdAt = null);


    /**
     * Get Expiry
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getExpiry();

    /**
     * Set Updated At
     *
     * @param string|null $expiry
     * @return $this
     * @since 100.1.0
     */
    public function setExpiry($expiry = null);


    /**
     * Get Expiry Email Sent
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getExpiryEmailSent();

    /**
     * Set Expiry Email Sent
     *
     * @param string|null $expiryEmailSent
     * @return $this
     * @since 100.1.0
     */
    public function setExpiryEmailSent($expiryEmailSent = null);


    /**
     * Get Version
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getVersion();

    /**
     * Set Version
     *
     * @param int|null $version
     * @return $this
     * @since 100.1.0
     */
    public function setVersion($version = null);


    /**
     * Get Old quote
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getOldQuote();

    /**
     * Set Old quote
     *
     * @param int|null $oldQuote
     * @return $this
     * @since 100.1.0
     */
    public function setOldQuote($oldQuote = null);



    /**
     * Get IS Admin Submitted
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getIsAdminSubmitted();

    /**
     * Set IS Admin Submitted
     *
     * @param int|null $isAdminSubmitted
     * @return $this
     * @since 100.1.0
     */
    public function setIsAdminSubmitted($isAdminSubmitted = null);


    /**
     * Get Move checkout
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getMoveCheckout();

    /**
     * Set Move Checkout
     *
     * @param int|null $moveCheckout
     * @return $this
     * @since 100.1.0
     */
    public function setMoveCheckout($moveCheckout = null);


    /**
     * Get Customer Name
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getCustomerName();

    /**
     * Set Customer Name
     *
     * @param string|null $customerName
     * @return $this
     * @since 100.1.0
     */
    public function setCustomerName($customerName = null);

    /**
     * Get Is Guest
     *
     * @return boolean|null
     * @since 100.1.0
     */
    public function getCustomerIsGuest();

    /**
     * Set Is Guest
     *
     * @param boolean|null $isGuest
     * @return $this
     * @since 100.1.0
     */
    public function setCustomerIsGuest($isGuest = null);
}
