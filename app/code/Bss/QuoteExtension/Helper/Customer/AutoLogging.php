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
namespace Bss\QuoteExtension\Helper\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AutoLogging
 *
 * @package Bss\QuoteExtension\Helper\Customer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AutoLogging extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PATH_REQUEST4QUOTE_AUTO_LOGGING = 'bss_request4quote/request4quote_global/auto_login';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * AutoLogging constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    /**
     * Is Autto logged In config admin
     *
     * @param int $store
     * @return bool
     */
    public function isAutoLogging($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_REQUEST4QUOTE_AUTO_LOGGING,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Website Id By storeview
     *
     * @param int $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();
        return $websiteId;
    }

    /**
     * Get Customer By Email
     *
     * @param string $email
     * @param int $storeId
     * @return \Magento\Customer\Model\Customer
     * @throws LocalizedException
     */
    public function getCustomer($email, $storeId)
    {
        $websiteId = $this->getWebsiteId($storeId);
        return $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
    }

    /**
     * Set Customer Data Logged in
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @throws LocalizedException
     */
    public function setCustomerDataLoggin($quote)
    {
        try {
            if ($quote->getId()) {
                $customerEmail = $quote->getCustomerEmail();
                $storeId = $quote->getStoreId();
                $customer = $this->getCustomer($customerEmail, $storeId);
                $this->customerSession->setCustomerAsLoggedIn($customer);
            }
        } catch (LocalizedException|\Exception $e) {
            $this->_logger->debug($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t let you see the quote now.'));
        }
    }

    /**
     * Check Customer Is Logged in
     *
     * @return bool
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }
}
