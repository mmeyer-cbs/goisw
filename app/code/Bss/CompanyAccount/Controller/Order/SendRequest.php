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

namespace Bss\CompanyAccount\Controller\Order;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Model\SubUserQuoteFactory;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;

/**
 * Class SendRequest
 *
 * @package Bss\CompanyAccount\Controller\Quote
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class SendRequest extends Action
{
    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteFactory
     */
    protected $quoteEntity;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var EmailHelper
     */
    protected $emailHelper;

    /**
     * @var SubUserQuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    protected $subQuoteRepo;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param PermissionsChecker $permissionsChecker
     * @param QuoteFactory $quoteEntity
     * @param Data $helper
     * @param Url $url
     * @param EmailHelper $emailHelper
     * @param SubUserQuoteFactory $quoteFactory
     * @param SubUserQuoteRepositoryInterface $subQuoteRepo
     * @param Cart $cart
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context                         $context,
        CheckoutSession                 $checkoutSession,
        PermissionsChecker              $permissionsChecker,
        QuoteFactory                    $quoteEntity,
        Data                            $helper,
        Url                             $url,
        EmailHelper                     $emailHelper,
        SubUserQuoteFactory             $quoteFactory,
        SubUserQuoteRepositoryInterface $subQuoteRepo,
        Cart                            $cart,
        LoggerInterface                 $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->permissionsChecker = $permissionsChecker;
        $this->quoteEntity = $quoteEntity;
        $this->helper = $helper;
        $this->url = $url;
        $this->emailHelper = $emailHelper;
        $this->quoteFactory = $quoteFactory;
        $this->subQuoteRepo = $subQuoteRepo;
        $this->cart = $cart;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Store current quote as order request
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->checkQuoteRequestOrder()) {
            $quoteId = $this->checkoutSession->getData('is_quote_extension');
            $quote = $this->quoteEntity->create()->load($quoteId);
            $this->cart->setQuote($quote);
        }
        $cartAmount = $this->cart->getQuote()->getBaseSubtotal();
        $checkValue = $this->permissionsChecker->isDenied(Permissions::PLACE_ORDER_WAITING);
        $orderAmount = $this->permissionsChecker->isDenied(Permissions::MAX_ORDER_AMOUNT, $cartAmount);
        $orderCount = $this->permissionsChecker->isDenied(Permissions::MAX_ORDER_PERDAY);
        if ((count($orderAmount) == 2 || count($orderCount)) == 2
            && ($orderAmount['is_denied'] || $orderCount['is_denied'])) {
            return $this->permissionsChecker->checkOrderPermission();
        }
        if (!$checkValue) {
            $customer = $this->helper->getCustomerSession()->getCustomer();
            $subUser = $this->helper->getCustomerSession()->getSubUser();
            if ($this->checkQuoteRequestOrder()) {
                $quoteId = (int)$this->cart->getQuote()->getId();
                $subUser->setQuoteId($quoteId);
            }
            $this->generateOrderRequest($subUser);
            if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_ORDER_ENABLED)
                && $this->helper->isSendEmailEnable('order')) {
                $message = $this->emailHelper->sendOrderRequestToAdmin($customer, $subUser);
                if ($message !== '') {
                    $this->permissionsChecker->getMessageManager()->addErrorMessage(__($message));
                }
            }
            $this->permissionsChecker->getMessageManager()->addSuccessMessage(
                __('Your Order Request has been sent for Company Admin, please check My orders Dashboard')
            );
            return $this->resultRedirectFactory->create()->setPath('sales/order/history/');
        } else {
            $this->permissionsChecker->getMessageManager()->addErrorMessage(
                __('Errors!!!! Please sign in again or contact admin for more information.')
            );
            return $this->resultRedirectFactory->create()->setPath('');
        }
    }

    /**
     * Function set data sub quote
     *
     * @param $subUser
     * @return void
     */
    public function generateOrderRequest($subUser)
    {
        try {
            $subQuote = $this->subQuoteRepo->getByQuoteId($subUser->getQuoteId());
            if (!$subQuote) {
                $subQuote = $this->quoteFactory->create();
            }
            $subQuote->setSubId(
                $subUser->getId()
            )->setQuoteId(
                $subUser->getQuoteId()
            )->setQuoteStatus(
                'waiting'
            )->setIsBackQuote(null);
            $this->subQuoteRepo->save($subQuote);
            $currentQuote = $this->quoteEntity->create()->load($subUser->getQuoteId());
            $currentQuote->setIsActive('0')->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Check request send order is quote?
     *
     * @return bool
     */
    public function checkQuoteRequestOrder()
    {
        $quoteRequest = $this->getRequest()->getServer('HTTP_REFERER') ?? "";
        return str_contains($quoteRequest, 'quote_id');
    }
}
