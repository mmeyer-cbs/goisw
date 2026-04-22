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

use Bss\QuoteExtension\Api\PlaceQuoteInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PlaceQuote
 *
 * @package Bss\QuoteExtension\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PlaceQuote implements PlaceQuoteInterface
{
    /**
     * @var Customer
     */
    protected $customerQuoteExtension;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\SalesSequence\Model\Manager
     */
    protected $sequenceManager;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote
     */
    protected $expiredQuote;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Bss\QuoteExtension\Model\ModuleCompatible
     */
    protected $moduleCompatible;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var string|int
     */
    protected $addressesToSync;

    /**
     * PlaceQuote constructor.
     *
     * @param Customer $customerQuoteExtension
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ManageQuote $manageQuote
     * @param LoggerInterface $logger
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $expiredQuote
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Bss\QuoteExtension\Model\ModuleCompatible $moduleCompatible
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        \Bss\QuoteExtension\Model\Customer $customerQuoteExtension,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        LoggerInterface $logger,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $expiredQuote,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bss\QuoteExtension\Model\ModuleCompatible $moduleCompatible,
        \Magento\Customer\Model\Session $session
    ) {
        $this->customerQuoteExtension = $customerQuoteExtension;
        $this->quoteRepository = $quoteRepository;
        $this->manageQuote = $manageQuote;
        $this->logger = $logger;
        $this->sequenceManager = $sequenceManager;
        $this->expiredQuote = $expiredQuote;
        $this->addressRepository = $addressRepository;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->moduleCompatible = $moduleCompatible;
        $this->session = $session;
    }

    /**
     * Set shipping information and place quote for a specified quote cart.
     *
     * @inheridoc
     * @param int $cartId
     * @param string|null $customerNote
     * @param ShippingMethodInterface|string|null $shippingMethod
     * @param AddressInterface|null $shippingAddress
     * @return int|void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function saveShippingInformationAndPlaceQuote(
        $cartId,
        $customerNote = null,
        $shippingMethod = null,
        $shippingAddress = null,
        $additional = null
    ) {
        $quote = $this->quoteRepository->get($cartId);
        if ($this->helper->isRequiredAddress()) {
            if (!$shippingAddress) {
                $shippingAddress = $quote->getShippingAddress();
            }
            if (!$shippingAddress->getCountryId()) {
                throw new StateException(__('The shipping address is missing. Set the address and try again.'));
            }
        }
        $ip = $this->expiredQuote->gretemoteAddress();
        if (!is_string($shippingMethod)) {
            if (!$shippingMethod) {
                $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            } else {
                $shippingMethod = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
            }
        }
        try {
            $quote->setRemoteIp($ip);
            $quote->setCustomerNote($customerNote);
            if ($this->helper->isRequiredAddress()) {
                $address = $shippingAddress->getData();
                $quote->getShippingAddress()->addData($address)
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($shippingMethod);
                $quote->collectTotals();
            }
            $quote->setIsActive(false);
            $this->saveQuoteWithNotLogin($additional, $quote);
            $quoteItems = $this->quoteRepository->get($quote->getId());
            $this->quoteRepository->save($quote);
            $this->_prepareCustomerQuote($quote);
            $incrementId = $this->sequenceManager->getSequence(
                'quote_extension',
                $quote->getStoreId()
            )->getNextValue();

            $customer = $quote->getCustomer();
            $currentTime = $this->helper->getCurrentDateTime();
            $expiry = $this->expiredQuote->calculatorExpiredDay($currentTime);

            $data = [
                'quote_id'     => $quote->getId(),
                'increment_id' => $incrementId,
                'expiry'       => $expiry,
                'status'       => $this->helper->returnPendingStatus(),
                'token'        => $this->helper->generateRandomString('30'),
                'email'        => $customer ? $quote->getCustomerEmail() : $customer->getEmail(),
                'customer_id'  => $customer ? $quote->getCustomerId() : '',
                'store_id'     => $quote->getStoreId(),
                'version'      => 0,
                'customer_name' => $this->customerQuoteExtension->getCustomerName($customer)
            ];

            /*Compatible with company account .Set sub_user id to Quote Extension*/
            if ($this->moduleCompatible->isEnableCompanyAccount($quote->getStore()->getWebsiteId())
                && isset($this->session->getData()['sub_user'])
            ) {
                $subUser = $this->session->getData()['sub_user'];
                $data['sub_user_id'] = $subUser->getId();
            }

            $this->manageQuote->setData($data);
            $this->saveQuoteExtensionWithNotLogin($additional, $quote);
            $this->manageQuote->save();
            $this->checkoutSession->setLastManaQuoteExtensionId($this->manageQuote->getEntityId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The quote cannot placed. Verify the input data and try again.')
            );
        }
        return $this->manageQuote->getEntityId();
    }

    /**
     * Add information guest into quote extension
     *
     * @param array $additional
     * @param \Bss\QuoteExtension\Model\QuoteExtension $quote
     */
    public function saveQuoteExtensionWithNotLogin($additional, $quote)
    {
        if (!$quote->getCustomerId()) {
            $this->manageQuote->setEmail($additional["email"]);
            $this->manageQuote->setCustomerEmail($additional["email"]);
            $this->manageQuote->setCustomerName($additional["customer_firstname"] . " " . $additional["customer_lastname"]);
            $this->manageQuote->setCustomerIsGuest(1);
        }
    }

    /**
     * Add information guest into quote
     *
     * @param array $additional
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function saveQuoteWithNotLogin($additional, $quote)
    {
        if (!$quote->getCustomerId()) {
            $quote->setCustomerEmail($additional["email"]);
            $quote->setCustomerFirstname($additional["customer_firstname"]);
            $quote->setCustomerLastname($additional["customer_lastname"]);
        }
    }

    /**
     * Validate quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @throws InputException
     * @throws NoSuchEntityException
     * @return void
     */
    protected function validateQuote(\Magento\Quote\Model\Quote $quote)
    {
        if (0 == $quote->getItemsCount()) {
            throw new InputException(
                __("The shipping method can't be set for an empty cart. Add an item to cart and try again.")
            );
        }
    }

    /**
     * Prepare quote for customer order submit
     *
     * @param Quote $quote
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareCustomerQuote($quote)
    {
        if (!$quote->getCustomerId()) {
            return;
        }
        /** @var Quote $quote */
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $this->customerQuoteExtension->getCustomerById($quote->getCustomerId());
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();

        if ($shipping && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                //Make provided address as default shipping address
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
                if (!$hasDefaultBilling && !$billing->getSaveInAddressBook()) {
                    $shippingAddress->setIsDefaultBilling(true);
                    $hasDefaultBilling = true;
                }
            }
            //save here new customer address
            $shippingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($shippingAddress);
            $quote->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
            $this->addressesToSync[] = $shippingAddress->getId();
            $shipping->setCustomerAddressId($shippingAddress->getId());
        }

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                //Make provided address as default shipping address
                if (!$hasDefaultShipping) {
                    //Make provided address as default shipping address
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $billingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($billingAddress);
            $quote->addCustomerAddress($billingAddress);
            $billing->setCustomerAddressData($billingAddress);
            $this->addressesToSync[] = $billingAddress->getId();
            $billing->setCustomerAddressId($billingAddress->getId());
        }
        if ($shipping && !$shipping->getCustomerId() && !$hasDefaultBilling) {
            $shipping->setIsDefaultBilling(true);
        }
    }
}
