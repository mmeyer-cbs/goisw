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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

/**
 * Class ManageQuote
 *
 * @package Bss\QuoteExtension\Model
 */
class ManageQuote extends \Magento\Framework\Model\AbstractModel implements \Bss\QuoteExtension\Api\Data\ManageQuoteInterface
{
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'quote_extension';

    /**
     * { @inheritDoc }
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\QuoteExtension\Model\ResourceModel\ManageQuote::class);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheridoc
     */
    public function setQuoteId($quoteId = null)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheridoc
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheridoc
     */
    public function getBackendQuoteId()
    {
        return $this->getData(self::BACKEND_QUOTE_ID);
    }

    /**
     * @inheridoc
     */
    public function setBackendQuoteId($backendQuoteId = null)
    {
        return $this->setData(self::BACKEND_QUOTE_ID, $backendQuoteId);
    }

    /**
     * @inheridoc
     */
    public function getTargetQuote()
    {
        return $this->getData(self::TARGET_QUOTE);
    }

    /**
     * @inheridoc
     */
    public function setTargetQuote($targetQuote = null)
    {
        return $this->setData(self::TARGET_QUOTE, $targetQuote);
    }

    /**
     * @inheridoc
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @inheridoc
     */
    public function setIncrementId($incrementId = null)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheridoc
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheridoc
     */
    public function setStoreId($storeId = null)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheridoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheridoc
     */
    public function setCustomerId($customerId = null)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheridoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheridoc
     */
    public function setStatus($status = null)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheridoc
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * @inheridoc
     */
    public function setToken($token = null)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * @inheridoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheridoc
     */
    public function setEmail($email = null)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheridoc
     */
    public function getEmailSent()
    {
        return $this->getData(self::EMAIL_SENT);
    }

    /**
     * @inheridoc
     */
    public function setEmailSent($emailSent = null)
    {
        return $this->setData(self::EMAIL_SENT, $emailSent);
    }

    /**
     * @inheridoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheridoc
     */
    public function setUpdatedAt($updatedAt = null)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheridoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheridoc
     */
    public function setCreatedAt($createdAt = null)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheridoc
     */
    public function getExpiry()
    {
        return $this->getData(self::EXPIRY);
    }

    /**
     * @inheridoc
     */
    public function setExpiry($expiry = null)
    {
        return $this->setData(self::EXPIRY, $expiry);
    }

    /**
     * @inheridoc
     */
    public function getExpiryEmailSent()
    {
        return $this->getData(self::EXPIRY_EMAIL_SENT);
    }

    /**
     * @inheridoc
     */
    public function setExpiryEmailSent($expiryEmailSent = null)
    {
        return $this->setData(self::EXPIRY_EMAIL_SENT, $expiryEmailSent);
    }

    /**
     * @inheridoc
     */
    public function getVersion()
    {
        return $this->getData(self::VERSION);
    }

    /**
     * @inheridoc
     */
    public function setVersion($version = null)
    {
        return $this->setData(self::VERSION, $version);
    }

    /**
     * @inheridoc
     */
    public function getOldQuote()
    {
        return $this->getData(self::OLD_QUOTE);
    }

    /**
     * @inheridoc
     */
    public function setOldQuote($oldQuote = null)
    {
        return $this->setData(self::OLD_QUOTE, $oldQuote);
    }

    /**
     * @inheridoc
     */
    public function getIsAdminSubmitted()
    {
        return $this->getData(self::IS_ADMIN_SUBMTITED);
    }

    /**
     * @inheridoc
     */
    public function setIsAdminSubmitted($isAdminSubmitted = null)
    {
        return $this->setData(self::IS_ADMIN_SUBMTITED, $isAdminSubmitted);
    }

    /**
     * @inheridoc
     */
    public function getMoveCheckout()
    {
        return $this->getData(self::MOVE_CHECKOUT);
    }

    /**
     * @inheridoc
     */
    public function setMoveCheckout($moveCheckout = null)
    {
        return $this->setData(self::MOVE_CHECKOUT, $moveCheckout);
    }

    /**
     * @inheridoc
     */
    public function getCustomerName()
    {
        return $this->getData(self::CUSTOMER_NAME);
    }

    /**
     * @inheridoc
     */
    public function setCustomerName($customerName = null)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * @inheridoc
     */
    public function getCustomerIsGuest()
    {
        return $this->getData(self::CUSTOMER_IS_GUEST);
    }

    /**
     * @inheridoc
     */
    public function setCustomerIsGuest($isGuest = null)
    {
        return $this->setData(self::CUSTOMER_IS_GUEST, $isGuest);
    }
}
