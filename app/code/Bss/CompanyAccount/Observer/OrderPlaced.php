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

namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserOrderInterface;
use Bss\CompanyAccount\Api\Data\SubUserOrderInterfaceFactory;
use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface as SubUserQuoteRepo;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\QuoteHelper;
use Bss\CompanyAccount\Helper\Tabs as TabsOrder;
use Bss\CompanyAccount\Model\QuoteExtensionFactory;
use Bss\CompanyAccount\Model\ResourceModel\QuoteExtension\CollectionFactory as QuoteExtensionCollection;
use Bss\CompanyAccount\Model\ResourceModel\SubUserOrder\CollectionFactory as SubUserOrderCollection;
use Bss\CompanyAccount\Model\SubUserOrderService;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class OrderPlaced
 *
 * @package Bss\CompanyAccount\Observer
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class OrderPlaced implements ObserverInterface
{
    /**
     * @var QuoteExtensionCollection
     */
    protected $quoteExtensionCollecion;

    /**
     * @var QuoteExtensionFactory
     */
    protected $quoteExtension;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var SubUserOrderCollection
     */
    protected $subUserCollection;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubUserOrderInterfaceFactory
     */
    private $userOrderFactory;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $userOrderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var SubUserQuoteRepo
     */
    private $subUserQuoteRepo;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Onepage
     */
    protected $onePage;

    /**
     * @var EmailHelper
     */
    protected $emailHelper;

    /**
     * @var TabsOrder
     */
    protected $tabsHelper;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var CheckoutSession
     */
    private $coSession;

    /**
     * OrderPlaced constructor.
     *
     * @param QuoteExtensionCollection $quoteExtensionCollecion
     * @param QuoteExtensionFactory $quoteExtension
     * @param State $state
     * @param CartRepositoryInterface $quoteRepository
     * @param SubUserOrderCollection $subUserCollection
     * @param LoggerInterface $logger
     * @param Json $serializer
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubUserOrderInterfaceFactory $userOrderFactory
     * @param SubUserOrderRepositoryInterface $userOrderRepository
     * @param Data $helper
     * @param SubUserQuoteRepo $subUserQuoteRepo
     * @param QuoteFactory $quoteFactory
     * @param Onepage $onePage
     * @param EmailHelper $emailHelper
     * @param TabsOrder $tabsHelper
     * @param QuoteHelper $quoteHelper
     * @param CheckoutSession $coSession
     */
    public function __construct(
        QuoteExtensionCollection        $quoteExtensionCollecion,
        QuoteExtensionFactory           $quoteExtension,
        State                           $state,
        CartRepositoryInterface         $quoteRepository,
        SubUserOrderCollection          $subUserCollection,
        LoggerInterface                 $logger,
        Json                            $serializer,
        SubUserRepositoryInterface      $subUserRepository,
        SubUserOrderInterfaceFactory    $userOrderFactory,
        SubUserOrderRepositoryInterface $userOrderRepository,
        Data                            $helper,
        SubUserQuoteRepo                $subUserQuoteRepo,
        QuoteFactory                    $quoteFactory,
        Onepage                         $onePage,
        EmailHelper                     $emailHelper,
        TabsOrder                       $tabsHelper,
        QuoteHelper                     $quoteHelper,
        CheckoutSession                 $coSession
    ) {
        $this->quoteExtensionCollecion = $quoteExtensionCollecion;
        $this->quoteExtension = $quoteExtension;
        $this->state = $state;
        $this->quoteRepository = $quoteRepository;
        $this->subUserCollection = $subUserCollection;
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->userOrderFactory = $userOrderFactory;
        $this->userOrderRepository = $userOrderRepository;
        $this->logger = $logger;
        $this->subUserRepository = $subUserRepository;
        $this->serializer = $serializer;
        $this->subUserQuoteRepo = $subUserQuoteRepo;
        $this->quoteFactory = $quoteFactory;
        $this->onePage = $onePage;
        $this->emailHelper = $emailHelper;
        $this->tabsHelper = $tabsHelper;
        $this->quoteHelper = $quoteHelper;
        $this->coSession = $coSession;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getOrder();
        $customer = $this->customerSession->getCustomer();
        $subUser = $this->customerSession->getSubUser();
        if ($this->state->getAreaCode() == "adminhtml") {
            $this->saveOrderQuoteBackend($order);
        } else {
            try {
                if ($this->helper->isEnable() && $subUser) {
                    /** @var SubUserOrderInterface $userOrder */
                    $subUserId = $subUser->getSubId();
                    $orderId = $order->getEntityId();
                    $orderIncrement = $order->getIncrementId();
                    if (!$this->checkExitsSubUserOrder($subUserId, $orderId)) {
                        if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_ORDER_ENABLED)
                            && $this->helper->isSendEmailEnable('order')
                        ) {
                            $mess = $this->emailHelper->sendOrderConfirmToAdmin($customer, $orderIncrement, $orderId);
                            if ($mess !== '') {
                                $this->helper->getMessageManager()->addErrorMessage(__($mess));
                            }
                        }
                        $this->saveSubUserOrder($subUser, $order);
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
        $this->getCurrentQuote($customer, $subUser);
    }

    /**
     * Function get back to current customer quote after checkout approved order
     *
     * @param Customer $customer
     * @param SubUserInterface $subUser
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentQuote($customer, $subUser)
    {
        if ($this->helper->isCompanyAccount()) {
            ($subUser) ?
                $backQuote = $this->quoteHelper->getBackQuote(
                    $subUser->getId(),
                    SubUserQuoteInterface::SUB_USER_ID
                ) : $backQuote = $this->quoteHelper->getBackQuote(
                    $customer->getId(),
                    SubUserQuoteInterface::CUSTOMER_ID
                );
            if (
                $backQuote
                && $backQuote->getQuoteId() !== $this->coSession->getQuoteId()
                && $this->coSession->getQuoteId() !== null
            ) {
                $this->setStatus($this->coSession->getQuoteId());
                if ($subUser) {
                    $subUser->setQuoteId((int)$backQuote->getQuoteId());
                    $this->subUserRepository->save($subUser);
                    $this->quoteHelper->removeUnsusedQuote($this->customerSession->getCustomerId(), $subUser);
                }
                $currentQuote = $this->quoteFactory->create()->load($backQuote->getQuoteId());
                $currentQuote->setIsActive(1)->save();
                $this->onePage->getCheckout()->replaceQuote($currentQuote);
            }
        }
    }

    /**
     * Function set bss sub quote status
     *
     * @param $approveQuoteId
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function setStatus($approveQuoteId)
    {
        $approveQuote = $this->subUserQuoteRepo->getByQuoteId($approveQuoteId);
        if ($approveQuote) {
            $subUserId = $this->customerSession->getSubUser();
            if (!$this->tabsHelper->sendOrderWaiting()) {
                if ($subUserId == null) {
                    $approveQuote->setActionBy(0);
                } else {
                    $approveQuote->setActionBy($subUserId->getSubId());
                }
            }
            $approveQuote->setQuoteStatus('approved');
            $this->subUserQuoteRepo->save($approveQuote);
        }
    }

    /**
     * Save Sub User Order
     *
     * @param SubUserInterface $subUser
     * @param OrderInterface $order
     * @deprecated v1.0.7 use SubUserOrderService instead
     * @see SubUserOrderService
     */
    public function saveSubUserOrder($subUser, $order)
    {
        try {
            $subUserId = $subUser->getSubId();
            $orderId = $order->getEntityId();
            $userOrder = $this->userOrderFactory->create();
            $userOrder->setSubId($subUserId);
            $userOrder->setOrderId($orderId);
            $userOrder->setGrandTotal($order->getBaseGrandTotal());
            $subUser = $this->subUserRepository->getById($subUserId);
            $subUserInfo[SubUserInterface::NAME] = $subUser->getSubName();
            $subUserInfo[SubUserInterface::EMAIL] = $subUser->getSubEmail();
            $subUserInfo['role_name'] = $subUser->getData('role_name');
            $subUserInfo['order_request'] = $subUser->getQuoteId();
            $userOrder->setSubUserInfo(
                $this->serializer->serialize($subUserInfo)
            );
            $this->userOrderRepository->save($userOrder);
        } catch (CouldNotSaveException $e) {
            $this->addErrorMsg($e->getMessage());
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->addErrorMsg(__('Something went wrong. Please try again later.'));
        }
    }

    /**
     * Add error message
     *
     * @param string $text
     */
    protected function addErrorMsg($text)
    {
        $this->helper->getMessageManager()->addErrorMessage($text);
    }

    /**
     * Check exits sub user order
     *
     * @param string $subUserId
     * @param string $orderId
     * @return bool
     * @deprecated v1.0.7 use SubUserOrderService instead
     * @see SubUserOrderService
     */
    public function checkExitsSubUserOrder($subUserId, $orderId)
    {
        $subUserCollection = $this->subUserCollection->create()
            ->addFieldToFilter('sub_id', $subUserId)
            ->addFieldToFilter('order_id', $orderId);
        if ($subUserCollection->getSize() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Save table order_created_by_admin
     *
     * @param OrderInterface $order
     */
    public function saveOrderQuoteBackend($order)
    {
        try {
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote->getQuoteExtension()) {
                $quoteExtensionCollection = $this->quoteExtensionCollecion->create()
                    ->addFieldToFilter('backend_quote_id', $quoteId)->getLastItem();
                $subUserId = $quoteExtensionCollection->getSubId();
                if ($subUserId) {
                    $subUser = $this->subUserRepository->getById($subUserId);
                    $this->saveSubUserOrder($subUser, $order);
                }
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
