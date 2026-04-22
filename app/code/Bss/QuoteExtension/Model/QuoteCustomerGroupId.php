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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class QuoteCustomerGroupId
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteCustomerGroupId
{
    const PATH_SAVE_CUSTOMER = 'bss_request4quote/general/save_customer';

    /**
     * @var string
     */
    public $area;

    /**
     * @var CollectionFactory
     */
    protected $quoteExtensionCollection;

    /**
     * @var CustomerInterface
     */
    protected $getCustomerById;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customer;

    /**
     * @var ShareConfig
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CollectionFactory $quoteExtensionCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\SessionFactory $customer
     * @param StoreManagerInterface $storeManager
     * @param ShareConfig $config
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory $quoteExtensionCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\SessionFactory $customer,
        StoreManagerInterface $storeManager,
        ShareConfig $config,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteExtensionCollection = $quoteExtensionCollection;
        $this->scopeConfig = $scopeConfig;
        $this->customerRepository = $customerRepository;
        $this->customer = $customer;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Save quote when customer change group id
     *
     * @param CartInterface $quoteMagento
     * @param CustomerInterface $customer
     * @return CartInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveQuoteCustomerGroupId($quoteMagento, $customer)
    {
        if ($customer) {
            try {
                if ($customer->getGroupId() !== $quoteMagento->getCustomerGroupId()) {
                    $quoteMagento = $this->quoteRepository->get($quoteMagento->getId());
                    /**
                     * It is needed to process customer's quotes for all websites
                     * if customer accounts are shared between all of them
                     */
                    /** @var $websites \Magento\Store\Model\Website[] */
                    $websites = $this->config->isWebsiteScope()
                        ? [$this->storeManager->getWebsite($customer->getWebsiteId())]
                        : $this->storeManager->getWebsites();

                    foreach ($websites as $website) {
                        $quoteMagento->setWebsite($website);
                        $quoteMagento->setCustomerGroupId($customer->getGroupId());
                        $quoteMagento->collectTotals();
                        $this->quoteRepository->save($quoteMagento);
                    }
                }
            } catch (NoSuchEntityException $e) {
                return $quoteMagento;
            }
        }
        return $quoteMagento;
    }

    /**
     * Get quote view
     *
     * @param ManageQuote|null $quoteExtension
     * @param \Magento\Quote\Model\Quote|CartInterface $quote
     * @param int $customerId
     * @return CartInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteView($quote, $customerId, $quoteExtension = null)
    {
        if (!$customerId) {
            return $quote;
        }
        if (!$quoteExtension) {
            if ($this->area == "backend") {
                $quoteExtension = $this->quoteExtensionCollection->create()->addFieldToFilter("quote_id", $quote->getId())->getLastItem();
            } else {
                $quoteExtension = $this->quoteExtensionCollection->create()->addFieldToFilter("backend_quote_id", $quote->getId())->getLastItem();
            }
        }

        if ($quote->getCustomerId() && $this->canChangeQuote($quoteExtension)) {
            $customer = $this->getCustomerById($customerId);
            $quote =  $this->saveQuoteCustomerGroupId($quote, $customer);
        }

        if ($quoteExtension) {
            $this->saveQuoteObserver($quoteExtension->getTargetQuote(), $quoteExtension->getCustomerId());
            $this->saveQuoteObserver($quoteExtension->getBackendQuoteId(), $quoteExtension->getCustomerId());
            $this->saveQuoteObserver($quoteExtension->getQuoteId(), $quoteExtension->getCustomerId());
        }

        return $quote;
    }


    /**
     * Save quote observer
     *
     * @param int $quoteId
     * @param int $customerId
     * @param CustomerInterface|null $customer
     * @return void
     */
    public function saveQuoteObserver($quoteId, $customerId, $customer = null)
    {
        if ($quoteId) {
            try {
                if (!$customer) {
                    $customer = $this->getCustomerById($customerId);
                }
                $quote = $this->quoteRepository->get($quoteId);
                if ($quote->getCustomerId()) {
                    $this->saveQuoteCustomerGroupId($quote, $customer);
                }
            } catch (\Exception $exception) {

            }
        }

    }

    /**
     * Get customer by id
     *
     * @param int $customerId
     * @return CustomerInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerById($customerId)
    {
        if (!$customerId) {
            return null;
        }
        if (!$this->getCustomerById) {
            try {
                $this->getCustomerById = $this->customerRepository->getById($customerId);
            } catch (\Exception $exception) {
                return null;
            }

        }
        return $this->getCustomerById;
    }

    /**
     * Can change quote
     *
     * @param ManageQuote $quoteExtension
     * @return bool
     */
    public function canChangeQuote($quoteExtension)
    {
        if (!$quoteExtension) {
            return true;
        }
        $ignore = [
            Status::STATE_CANCELED,
            Status::STATE_ORDERED ,
            Status::STATE_REJECTED,
            Status::STATE_COMPLETE,
            Status::STATE_EXPIRED
        ];
        if (in_array($quoteExtension->getStatus(), $ignore)) {
            return false;
        }
        return true;
    }

    /**
     * Set customer group id
     *
     * @param \Magento\Quote\Model\Quote|CartInterface $quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setCustomerGroupId($quote)
    {
        if ($quote->getCustomerId()) {
            $customer = $this->getCustomerById($quote->getCustomerId());
            if ($customer && $customer->getGroupId() !== $quote->getCustomerGroupId()) {
                $quote->setCustomerGroupId($customer->getGroupId());
            }
        }
    }

    /**
     * Get config save customer
     *
     * @return bool
     */
    public function isEnableConfigSaveCustomer()
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_SAVE_CUSTOMER
        );
    }

    /**
     * List status allow save quote when customer change customer group
     *
     * @return array
     */
    public function listStatusAllow()
    {
        return [
            \Bss\QuoteExtension\Model\Config\Source\Status::STATE_PENDING,
            \Bss\QuoteExtension\Model\Config\Source\Status::STATE_UPDATED,
            \Bss\QuoteExtension\Model\Config\Source\Status::STATE_RESUBMIT,
        ];
    }
}
