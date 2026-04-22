<?php
declare(strict_types = 1);

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

namespace Bss\CompanyAccount\Plugin\Checkout;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\QuoteHelper;
use Bss\CompanyAccount\Model\SubUserQuoteFactory;
use Bss\CompanyAccount\Model\SubUserQuoteRepository;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Psr\Log\LoggerInterface;

/**
 * Class Session
 *
 * @package Bss\CompanyAccount\Plugin\Checkout
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Session
{
    /**
     * @var String
     */
    const APPROVED_ORDER_CHECKOUT_REQUEST = 'bss_companyAccount_order_checkout';

    /**
     * @var SubUserQuoteFactory
     */
    protected $subQuoteModel;

    /**
     * Quote instance
     *
     * @var Quote
     */
    protected $quoteModel;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * A flag to track when the quote is being loaded and attached to the session object.
     *
     * Used in trigger_recollect infinite loop detection.
     *
     * @var bool
     */
    private $isLoading = false;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var RequestHttp
     */
    private $request;

    /**
     * @param bool
     */
    protected $isQuoteMasked;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    protected $subQuoteRepo;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Session constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param Data $helper
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param RemoteAddress $remoteAddress
     * @param RequestHttp $request
     * @param SubUserRepositoryInterface $subUserRepository
     * @param ManagerInterface $eventManager
     * @param CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param SubUserQuoteRepositoryInterface $subQuoteRepo
     * @param SubUserQuoteFactory $subQuoteModel
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(
        QuoteFactory                    $quoteFactory,
        Data                            $helper,
        QuoteIdMaskFactory              $quoteIdMaskFactory,
        RemoteAddress                   $remoteAddress,
        RequestHttp                     $request,
        SubUserRepositoryInterface      $subUserRepository,
        ManagerInterface                $eventManager,
        CartRepositoryInterface         $quoteRepository,
        \Magento\Customer\Model\Session $customerSession,
        SubUserQuoteRepositoryInterface $subQuoteRepo,
        SubUserQuoteFactory             $subQuoteModel,
        QuoteHelper                     $quoteHelper,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->helper = $helper;
        $this->storeManager = $this->helper->getStoreManager();
        $this->eventManager = $eventManager;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
        $this->subUserRepository = $subUserRepository;
        $this->subQuoteRepo = $subQuoteRepo;
        $this->subQuoteModel = $subQuoteModel;
        $this->quoteHelper = $quoteHelper;
        $this->logger = $logger;
    }

    /**
     * Check quote for sub-user
     *
     * @param \Magento\Checkout\Model\Session $subject
     * @param callable $proceed
     *
     * @return \Magento\Quote\Api\Data\CartInterface|Quote|mixed
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundGetQuote(\Magento\Checkout\Model\Session $subject, callable $proceed)
    {
        if ($this->helper->isCompanyAccount()) {
            /** @var SubUserInterface $subUser */
            $subUser = $this->customerSession->getSubUser();
            $customerId = $this->customerSession->getCustomerId();
            if ($subUser) {
                $this->eventManager->dispatch('sub_quote_process', ['checkout_session' => $this]);
                if ($this->quoteModel === null) {
                    if ($this->isLoading) {
                        throw new \LogicException("Infinite loop detected, review the trace for the looping path");
                    }
                    $this->isLoading = true;
                    /** @var \Magento\Quote\Api\Data\CartInterface|Quote $quote */
                    $quote = $this->quoteFactory->create();
                    if ($subject->getQuoteId()) {
                        try {
                            $quote = $this->quoteRepository->getActive($subject->getQuoteId());
                            if ($quote->getData('customer_id') &&
                                (int)$quote->getData('customer_id') !== (int)$customerId
                            ) {
                                $quote = $this->quoteFactory->create();
                                $subject->setQuoteId(null);
                            }

                            /**
                             * If current currency code of quote is not equal current currency code of store,
                             * need recalculate totals of quote. It is possible if customer use currency switcher or
                             * store switcher.
                             */
                            if ($quote->getQuoteCurrencyCode() !=
                                $this->storeManager->getStore()->getCurrentCurrencyCode()
                            ) {
                                $quote->setStore($this->storeManager->getStore());
                                $this->quoteRepository->save($quote->collectTotals());
                                /*
                                 * We must create new quote object, because collectTotals()
                                 * can to create links with other objects.
                                 */
                                $quote = $this->quoteRepository->get($subject->getQuoteId());
                            }

                            if ($quote->getTotalsCollectedFlag() === false) {
                                $quote->collectTotals();
                            }
                        } catch (NoSuchEntityException $e) {
                            $this->logger->critical($e);
                            $subject->setQuoteId(null);
                        }
                    }

                    if (!$subject->getQuoteId()) {
                        if ($this->customerSession->isLoggedIn()) {
                            $quoteBySubUser = $this->subUserRepository->getQuoteBySubUser($subUser);
                            if ($quoteBySubUser !== null) {
                                $subject->setQuoteId($quoteBySubUser->getId());
                                $quote = $quoteBySubUser;
                            }
                        } else {
                            $quote->setIsCheckoutCart(true);
                            $quote->setCustomerIsGuest(1);
                            $this->eventManager->dispatch('checkout_sub_quote_init', ['quote' => $quote]);
                        }
                    }

                    $quote->setStore($this->storeManager->getStore());
                    $quote->setData('bss_is_sub_quote', $customerId);
                    try {
                        $quote->setCustomer($this->customerSession->getCustomerDataObject());
                        $quote->save();
                        $subUser->setQuoteId((int)$quote->getId());
                        $this->subUserRepository->save($subUser);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                    $this->quoteModel = $quote;
                    $this->isLoading = false;
                }
                if (!$this->isQuoteMasked() && !$this->customerSession->isLoggedIn() && $subject->getQuoteId()) {
                    $quoteId = $subject->getQuoteId();

                    /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
                    $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
                    if ($quoteIdMask->getMaskedId() === null) {
                        $quoteIdMask->setQuoteId($quoteId)->save();
                    }
                    $this->setIsQuoteMasked(true);
                }
                $remoteAddress = $this->remoteAddress->getRemoteAddress();
                if ($remoteAddress) {
                    $this->quoteModel->setRemoteIp($remoteAddress);
                    $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
                    $this->quoteModel->setXForwardedFor($xForwardIp);
                }
                return $this->quoteModel;
            }
        }
        return $proceed();
    }

    /**
     * Check if logged in user is sub-user then create quote for this sub-user
     *
     * @param \Magento\Checkout\Model\Session $subject
     * @param callable $proceed
     * @return $this
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundLoadCustomerQuote(\Magento\Checkout\Model\Session $subject, callable $proceed)
    {
        /** @var SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();
        if ($subUser) {
            try {
                if ($subUser->getQuoteId()) {
                    /** @var \Magento\Quote\Api\Data\CartInterface $subUserQuote */
                    $subUserQuote = $this->subUserRepository->getQuoteBySubUser($subUser);
                    if (!$subUserQuote) {
                        $subUserQuote = $this->quoteFactory->create();
                    }
                } else {
                    $subUserQuote = $this->quoteFactory->create();
                }
                $subUserQuote->setStoreId($this->helper->getStoreManager()->getStore()->getId());
                if ($subUserQuote->getId() && $subject->getQuoteId() != $subUserQuote->getId()) {
                    if ($subject->getQuoteId()) {
                        $subUserQuote->setCustomerIsGuest(0);
                        $this->quoteRepository->save(
                            $subUserQuote->merge($subject->getQuote()->collectTotals())
                        );
                        $newQuote = $this->quoteRepository->get($subUserQuote->getId());
                        $this->quoteRepository->save(
                            $newQuote->collectTotals()
                        );
                        $subUserQuote = $newQuote;

                        $subject->setQuoteId($subUserQuote->getId());
                        if ($this->quoteModel) {
                            $this->quoteRepository->delete($this->quoteModel);
                        }
                        $this->quoteModel = $subUserQuote;
                    }
                } else {
                    $subject->getQuote()->getBillingAddress();
                    $subject->getQuote()->getShippingAddress();
                    $subject->getQuote()->setCustomer($this->customerSession->getCustomerDataObject())
                        ->setCustomerIsGuest(0)
                        ->setTotalsCollectedFlag(false)
                        ->collectTotals();
                    $this->quoteRepository->save($subject->getQuote());
                }
            } catch (\Exception $e) {
                $this->helper->getMessageManager()->addErrorMessage($e->getMessage());
            }
        } else {
            return $proceed();
        }
        return $this;
    }

    /**
     * Flag whether or not the quote uses a masked quote id
     *
     * @param bool $isQuoteMasked
     * @return void
     * @codeCoverageIgnore
     */
    protected function setIsQuoteMasked($isQuoteMasked)
    {
        $this->isQuoteMasked = $isQuoteMasked;
    }

    /**
     * Return if the quote has a masked quote id
     *
     * @return bool|null
     * @codeCoverageIgnore
     */
    protected function isQuoteMasked()
    {
        return $this->isQuoteMasked;
    }

    /**
     * Active approve quote to checkout session
     *
     * @param \Magento\Checkout\Model\Session $subject
     * @param $result
     * @return Quote|mixed
     * @throws \Exception
     * @return \Magento\Quote\Api\Data\CartInterface|Quote|mixed
     */
    public function afterGetQuote($subject, $result)
    {
        if ($this->helper->isCompanyAccount()) {
            $subUser = $this->customerSession->getSubUser();
            $customerId = $this->customerSession->getCustomerId();
            $backQuote = $this->getBackQuote($subUser, $customerId);
            if ($subject->getQuoteId() == null
                && $backQuote
                && $this->request->getFullActionName() !== 'checkout_cart_index'
            ) {
                $this->subQuoteRepo->delete($backQuote);
            }
            if (!$backQuote) {
                if ($subUser) {
                    $backQuote = $this->subQuoteModel->create(
                    )->setSubId(
                        $subUser->getId()
                    )->setIsBackQuote(
                        SubUserQuoteInterface::USER_BACK_QUOTE
                    )->setQuoteStatus(
                        'active'
                    )->setQuoteId($subject->getQuoteId());
                    $this->quoteHelper->removeSubQuotes($subUser->getId());
                } else {
                    $backQuote = $this->subQuoteModel->create(
                    )->setSubId(
                        0
                    )->setQuoteStatus(
                        'active'
                    )->setQuoteId($subject->getQuoteId());
                    $this->quoteHelper->removeSubQuotes(0);
                    if ($subject->getQuoteId() == null) {
                        $backQuote->setIsBackQuote(SubUserQuoteInterface::BACK_QUOTE_BLANK);
                    } else {
                        $backQuote->setIsBackQuote(SubUserQuoteInterface::ADMIN_BACK_QUOTE);
                    }
                }
                $this->subQuoteRepo->save($backQuote);
            }
            if ($backQuote->getQuoteId() == null) {
                if ($subUser || $backQuote->getIsBackQuote() != 0) {
                    $this->subQuoteRepo->delete($backQuote);
                }
            }
            if ($this->request->getFullActionName() == self::APPROVED_ORDER_CHECKOUT_REQUEST) {
                $savedQuote = $this->customerSession->getDisableQuote();
                if ($savedQuote && $subject->getQuoteId() != $savedQuote) {
                    $unusedQuote = $this->quoteRepository->get($savedQuote);
                    $unusedQuote->setIsActive(0);
                    $this->quoteRepository->save($unusedQuote);
                    $this->customerSession->setDisableQuote(null);
                } elseif ($subject->getQuoteId() != $backQuote->getQuoteId()) {
                    $this->customerSession->setDisableQuote($subject->getQuoteId());
                }
                $quoteId = $this->request->getParam('order_id');
                $quote = $this->quoteFactory->create()->load($quoteId);
                $quote->setIsActive(1)->save();
                return $quote;
            }
        }
        return $result;
    }

    /**
     * Get back quote
     *
     * @param SubUserInterface $subUser
     * @param int $customerId
     * @return SubUserQuoteInterface|bool
     */
    private function getBackQuote($subUser, $customerId)
    {
        ($subUser) ?
            $backQuote = $this->subQuoteRepo->getByUserId(
                $subUser->getId(),
                SubUserQuoteRepository::FIELD_SUB_USER_ID
            ) : $backQuote = $this->subQuoteRepo->getByUserId(
                $customerId,
                SubUserQuoteRepository::FIELD_CUSTOMER_ID
            );
        return $backQuote;
    }
}
