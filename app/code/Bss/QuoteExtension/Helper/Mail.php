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
namespace Bss\QuoteExtension\Helper;

use Bss\QuoteExtension\Helper\QuoteExtension\Version;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as QuoteExtensionCollection;
use Exception;
use IntlDateFormatter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Mail
 *
 * @package Bss\QuoteExtension\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mail extends AbstractHelper
{
    const PATH_REQUEST4QUOTE_EMAIL_IDENTITY = 'bss_request4quote/request4quote_email_config/sender_email_identity';
    const PATH_REQUEST4QUOTE_EMAIL_COPY = 'bss_request4quote/request4quote_email_config/send_email_copy';
    const PATH_REQUEST4QUOTE_NEW_QUOTE = 'bss_request4quote/request4quote_email_config/new_quote_extension';
    const PATH_REQUEST4QUOTE_NEW_QUOTE_CUSTOMER = 'bss_request4quote/request4quote_email_config/new_quote_extension_customer';
    const PATH_REQUEST4QUOTE_QUOTE_CUSTOMER_GUEST = 'bss_request4quote/request4quote_email_config/quote_extension_customer_guest';
    const PATH_REQUEST4QUOTE_RECEIVE_EMAIL = 'bss_request4quote/request4quote_email_config/receive_email_identity';
    const PATH_REQUEST4QUOTE_QUOTE_ACCEPT = 'bss_request4quote/request4quote_email_config/quote_extension_accept';
    const PATH_REQUEST4QUOTE_QUOTE_COMPLETE = 'bss_request4quote/request4quote_email_config/quote_extension_complete';
    const PATH_REQUEST4QUOTE_CANCELLED = 'bss_request4quote/request4quote_email_config/quote_extension_cancelled';
    const PATH_REQUEST4QUOTE_QUOTE_REJECTED = 'bss_request4quote/request4quote_email_config/quote_extension_rejected';
    const PATH_REQUEST4QUOTE_QUOTE_EXPIRED = 'bss_request4quote/request4quote_email_config/quote_extension_expired';
    const PATH_REQUEST4QUOTE_QUOTE_ORDERED = 'bss_request4quote/request4quote_email_config/quote_extension_ordered';
    const PATH_REQUEST4QUOTE_QUOTE_RESUBMIT = 'bss_request4quote/request4quote_email_config/quote_extension_resubmit';
    const PATH_REQUEST4QUOTE_QUOTE_REMINDER = 'bss_request4quote/request4quote_email_config/quote_extension_reminder_expired';

    /**
     * @var \Bss\QuoteExtension\Model\QuoteEmail
     */
    protected $quoteEmail;

    /**
     * @var QuoteExtensionCollection
     */
    protected $quoteExtensionCollection;

    /**
     * @var array
     */
    protected $parentProductTypeList = ['configurable', 'grouped'];

    /**
     * @var StoreManagerInterface $storeManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var FormatDate
     */
    protected $emailData;

    /**
     * @var HidePriceEmail
     */
    protected $hidePriceEmail;

    /**
     * @var Version
     */
    protected $versionHelper;

    /**
     * Mail constructor.
     *
     * @param \Bss\QuoteExtension\Model\QuoteEmail $quoteEmail
     * @param QuoteExtensionCollection $quoteExtensionCollection
     * @param Context $context
     * @param StoreManagerInterface $storeManagerInterface
     * @param Data $helper
     * @param LayoutInterface $layout
     * @param StateInterface $inlineTranslation
     * @param ManagerInterface $messageManager
     * @param TransportBuilder $transportBuilder
     * @param SenderResolverInterface $senderResolver
     * @param FormatDate $emailData
     * @param HidePriceEmail $hidePriceEmail
     * @param Version $versionHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\QuoteExtension\Model\QuoteEmail $quoteEmail,
        QuoteExtensionCollection $quoteExtensionCollection,
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        Data $helper,
        LayoutInterface $layout,
        StateInterface $inlineTranslation,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        SenderResolverInterface $senderResolver,
        FormatDate $emailData,
        HidePriceEmail $hidePriceEmail,
        Version $versionHelper
    ) {
        $this->quoteEmail = $quoteEmail;
        $this->quoteExtensionCollection = $quoteExtensionCollection;
        parent::__construct($context);
        $this->storeManager = $storeManagerInterface;
        $this->helper = $helper;
        $this->layout = $layout;
        $this->inlineTranslation = $inlineTranslation;
        $this->messageManager = $messageManager;
        $this->transportBuilder = $transportBuilder;
        $this->senderResolver = $senderResolver;
        $this->emailData = $emailData;
        $this->hidePriceEmail = $hidePriceEmail;
        $this->versionHelper = $versionHelper;
    }

    /**
     * Get Sender Email
     *
     * @param int $storeId
     * @return mixed
     * @throws MailException
     */
    public function getEmailSender($storeId = null)
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get Sender Name
     *
     * @param int $storeId
     * @return mixed
     * @throws MailException
     */
    public function getEmailSenderName($storeId = null)
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }

    /**
     * Get Email copy to
     *
     * @param int $storeId
     * @return array
     */
    public function getEmailCoppy($storeId = null)
    {
        $sendEmailCoppys = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_EMAIL_COPY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($sendEmailCoppys != '') {
            return $this->helper->toArray($sendEmailCoppys);
        }
        return [];
    }

    /**
     * Get email for new quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailNewQuote($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_NEW_QUOTE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for new quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailNewQuoteForCustomer($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_NEW_QUOTE_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for new quote customer guest config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailQuoteForCustomerGuest($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_CUSTOMER_GUEST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for receive quote config
     *
     * @param int $storeId
     * @return mixed
     * @throws MailException
     */
    public function getEmailReceiveEmail($storeId = null)
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_RECEIVE_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get email for receive quote config
     *
     * @param int $storeId
     * @return mixed
     * @throws MailException
     */
    public function getEmailReceiveEmailName($storeId = null)
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_RECEIVE_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }

    /**
     * Get email for cancel quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailCancelledQuote($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_CANCELLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for reject quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailRejectedQuote($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_REJECTED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for resubmit quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailResubmitQuote($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_RESUBMIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for accept quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailAcceptQuote($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_ACCEPT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for complete quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailCompleteQuote($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_COMPLETE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for expired quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailQuoteExpried($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_EXPIRED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for ordered quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailQuoteOrdered($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_ORDERED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for ordered quote config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getEmailReminder($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_REMINDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Send new quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationNewQuoteEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $this->setCustomerName($quote);
        $templateName = $this->getEmailNewQuote($storeId);
        $senderEmail = $this->getEmailSender($storeId);
        if ($senderEmail) {
            $quote->setIsAdminNotification(true);
            $senderName = is_string(__('Customer ')) ? __('Customer ') : __('Customer ')->getText();
            $recipientEmail = $this->getEmailReceiveEmail($storeId);
            $this->quoteEmail->getQuoteById($quote->getEntityId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'created_at' => $this->emailData->getCreatedAtFormatted(
                    $requestQuote->getCreatedAt(),
                    $quote->getstore(),
                    IntlDateFormatter::MEDIUM
                ),
                'quote' => $quote
            ];
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send new quote email for customer
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationNewQuoteEmailForCustomer($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $templateName = $this->getTemplateCustomerOrGuest($quote);
        $senderEmail = $this->getEmailSender($storeId);
        $quote = $this->checkHidePrice($requestQuote, $quote);
        if ($senderEmail) {
            $quote->setIsAdminNotification(false);
            $senderName = $this->getEmailSenderName($storeId);
            $recipientEmail = $this->getUser($quote)["email"];
            $this->quoteEmail->getQuoteById($quote->getEntityId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'quote' => $quote,
                'created_at' => $this->emailData->getCreatedAtFormatted(
                    $requestQuote->getCreatedAt(),
                    $quote->getstore(),
                    IntlDateFormatter::MEDIUM
                )
            ];
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Get template customer login or not login submit quote extension
     *
     * @param CartInterface $quote
     * @return mixed
     */
    public function getTemplateCustomerOrGuest($quote)
    {
        if ($quote->getCustomerId()) {
            return  $this->getEmailNewQuoteForCustomer($quote->getStoreId());
        }
        return $this->getEmailQuoteForCustomerGuest($quote->getStoreId());
    }

    /**
     * Send accept quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function sendNotificationAcceptQuoteEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $this->setCustomerName($quote);
        $templateName = $this->getEmailAcceptQuote($storeId);
        $senderEmail = $this->getEmailSender($storeId);

        if ($senderEmail) {
            $recipientEmail = $this->getUser($quote)["email"];
            $requestQuoteUrl = $this->_getUrl(
                "quoteextension/quote/view",
                [
                    'quote_id' => $requestQuote->getId(),
                    'token' => $requestQuote->getToken()
                ]
            );
            $quote->setNeedHidePrice(false);
            $variables = $this->getVariables($requestQuote, $quote, $requestQuoteUrl);
            $comments = $this->versionHelper->getHistoryComment($requestQuote, 'admin');
            if ($comments) {
                $variables['comments'] = $comments;
            }
            $senderName = $this->getEmailSenderName($storeId);
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send complete quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function sendNotificationCompleteQuoteEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $this->setCustomerName($quote);
        $templateName = $this->getEmailCompleteQuote($storeId);
        $senderEmail = $this->getEmailSender($storeId);

        if ($senderEmail) {
            $recipientEmail = $this->getUser($quote)["email"];
            $requestQuoteUrl = $this->_getUrl(
                "quoteextension/quote/view",
                [
                    'quote_id' => $requestQuote->getId(),
                    'token' => $requestQuote->getToken()
                ]
            );
            $quote->setNeedHidePrice(false);
            $variables = $this->getVariables($requestQuote, $quote, $requestQuoteUrl);
            $comments = $this->versionHelper->getHistoryComment($requestQuote, 'admin');
            if ($comments) {
                $variables['comments'] = $comments;
            }
            $senderName = $this->getEmailSenderName($storeId);
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
        $requestQuote->setEmailSent(1);
    }

    /**
     * Send cancel quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteCancelledEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $currentDate = $this->emailData->getCurrentDate();
        $cancelDate = $this->emailData->getCreatedAtFormatted(
            $currentDate,
            $quote->getStore(),
            IntlDateFormatter::MEDIUM
        );
        $templateName = $this->getEmailCancelledQuote($storeId);
        $senderEmail = $this->getEmailSender($storeId);

        if ($senderEmail) {
            $senderName = $this->getEmailSenderName($storeId);
            $getUser = $this->getUser($quote);
            $recipientEmail = $getUser["email"];
            $recipientName = $getUser["name"];
            $this->quoteEmail->getQuoteById($quote->getEntityId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'cancelled_date' => $cancelDate,
                'customer_name' => $recipientName,
                'quote' => $quote
            ];
            $comments = $this->versionHelper->getHistoryComment($requestQuote, 'admin');
            if ($comments) {
                $variables['comments'] = $comments;
            }
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send ordered quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteOrderedEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $templateName = $this->getEmailQuoteOrdered($storeId);
        $senderEmail = $this->getEmailSender($storeId);

        if ($senderEmail) {
            $senderName = $this->getEmailSenderName($storeId);
            $recipientEmail = $this->getEmailReceiveEmail($storeId);
            $this->quoteEmail->getQuoteById($quote->getEntityId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'quote' => $quote
            ];
            $comments = $this->versionHelper->getHistoryComment($requestQuote, 'admin');
            if ($comments) {
                $variables['comments'] = $comments;
            }
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send reject quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteRejectedEmail($quote, $requestQuote)
    {
        $storeId = $requestQuote->getStoreId() ?? $this->storeManager->getStore()->getId();
        $currentDate = $this->emailData->getCurrentDate();
        $cancelDate = $this->emailData->getCreatedAtFormatted(
            $currentDate,
            $quote->getstore(),
            IntlDateFormatter::MEDIUM
        );
        $templateName = $this->getEmailRejectedQuote($storeId);
        $senderEmail = $this->getEmailSender($storeId);

        if ($requestQuote->getStatus() === Status::STATE_PENDING
            || $requestQuote->getStatus() === Status::STATE_CANCELED
            || $requestQuote->getStatus() === Status::STATE_REJECTED
        ) {
            foreach ($quote->getAllVisibleItems() as $item) {
                /* @var $item Item */
                $product = $item->getProduct();
                $item->setNeedCheckPrice(true);
                $item->setProduct($product);
                if ($item->getProductType() == 'configurable') {
                    $parentProductId = $item->getProductId();
                    $childProductSku = $item->getSku();
                    $canShowPrice = $this->hidePriceEmail->canShowPrice($parentProductId, $childProductSku);
                } else {
                    $canShowPrice = $this->hidePriceEmail->canShowPrice($item->getProductId(), false);
                }
                if (!$canShowPrice) {
                    $quote->setNeedHidePrice(true);
                    break;
                }
            }
        }

        $requestQuoteUrl = $this->_getUrl(
            "quoteextension/quote/view",
            [
                'quote_id' => $requestQuote->getId(),
                'token' => $requestQuote->getToken()
            ]
        );

        if ($senderEmail) {
            $senderName = $this->getEmailSenderName($storeId);
            $getUser = $this->getUser($quote);
            $recipientEmail = $getUser["email"];
            $recipientName = $getUser["name"];
            $this->quoteEmail->getQuoteById($quote->getEntityId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'cancelled_date' => $cancelDate,
                'customer_name' => $recipientName,
                'quote' => $quote,
                'request_quote' => $requestQuote,
                'request_url' => $requestQuoteUrl
            ];
            $comments = $this->versionHelper->getHistoryComment($requestQuote, 'admin');
            if ($comments) {
                $variables['comments'] = $comments;
            }
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send resubmit quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteReSubmitEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $this->setCustomerName($quote);
        $templateName = $this->getEmailResubmitQuote($storeId);
        $senderEmail = $this->getEmailSender($storeId);
        $updateAt = $requestQuote->getUpdatedAt();
        if ($senderEmail) {
            $quote->setIsAdminNotification(true);
            $senderName = $this->getEmailSenderName($storeId);
            $recipientEmail = $this->getEmailReceiveEmail($storeId);
            $recipientName = $this->helper->getCustomerName($requestQuote->getCustomerId());
            $this->quoteEmail->getQuoteById($quote->getEntityId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'update_date' => $updateAt,
                'customer_name' => $recipientName,
                'quote' => $quote
            ];
            $comments = $this->versionHelper->getHistoryComment($requestQuote, 'customer');
            if ($comments) {
                $variables['comments'] = $comments;
            }
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send reminder quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteReminderEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $templateName = $this->getEmailReminder($storeId);
        $senderEmail = $this->getEmailSender($storeId);
        $expiryDay = $requestQuote->getExpiry();
        if ($senderEmail) {
            $senderName = $this->getEmailSenderName($storeId);
            $recipientEmail = $this->getUser($quote)["email"];
            $recipientName = $this->helper->getCustomerName($requestQuote->getCustomerId());
            $this->quoteEmail->getQuoteById($quote->getEntityId());
            $this->_logger->debug($recipientName . ' ----------');
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'expired_day' => $expiryDay,
                'customer_name' => $recipientName,
                'quote' => $quote
            ];
            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);

            /* Send additional Email Reminder To Admin */
            if (is_array($recipientEmail)) {
                $recipientEmail[] = $this->getEmailReceiveEmail($storeId);
            } else {
                $recipientEmail = [$recipientEmail, $this->getEmailReceiveEmail($storeId)];
            }

            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send Expired Email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException|LocalizedException
     */
    public function sendNotificationExpiredEmail($quote, $requestQuote)
    {
        $storeId = $quote->getStore()->getId() ?? $this->storeManager->getStore()->getId();
        $this->setCustomerName($quote);
        $templateName = $this->getEmailQuoteExpried($storeId);
        $senderEmail = $this->getEmailSender($storeId);

        if ($senderEmail) {
            $recipientEmail = $this->getUser($quote)["email"];

            $url = $this->storeManager->getStore()->getUrl();

            $quote = $this->checkHidePrice($requestQuote, $quote);

            $this->quoteEmail->getQuoteById($quote->getEntityId());

            $recipientName = $this->helper->getCustomerName($requestQuote->getCustomerId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'created_at' => $this->emailData->getCreatedAtFormatted(
                    $requestQuote->getCreatedAt(),
                    $quote->getstore(),
                    IntlDateFormatter::MEDIUM
                ),
                'quote' => $quote,
                'purchase_link' => $url,
                'customer_name' => $recipientName,
                'expired_at' => $this->emailData->formatDate($requestQuote->getExpiry(), IntlDateFormatter::SHORT)
            ];
            $senderName = $this->getEmailSenderName($storeId);

            $recipientEmail = $this->getRecipientsEmail($recipientEmail, $storeId);

            /* Send additional Email Expired To Admin */
            if (is_array($recipientEmail)) {
                $recipientEmail[] = $this->getEmailReceiveEmail($storeId);
            } else {
                $recipientEmail = [$recipientEmail, $this->getEmailReceiveEmail($storeId)];
            }

            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Get other email to sender
     *
     * @param int $store
     * @param string|array $recipientEmail
     * @return array
     */
    protected function getRecipientsEmail($recipientEmail, $store = null)
    {
        $emailCoppys = $this->getEmailCoppy($store);
        if (!empty($emailCoppys)) {
            $emailCoppys[] = $recipientEmail;
            $receivers = $emailCoppys;
            return $receivers;
        }

        return $recipientEmail;
    }

    /**
     * Send Notification Email
     *
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string|array $recipientEmail
     * @param array $variables
     * @param int $storeId
     * @return bool
     */
    protected function send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $variables,
        $storeId
    ) {
        $this->inlineTranslation->suspend();
        try {
            if (is_array($recipientEmail)) {
                foreach ($recipientEmail as $recipient) {
                    $this->_send(
                        $templateName,
                        $senderName,
                        $senderEmail,
                        $recipient,
                        $variables,
                        $storeId
                    );
                }
            } else {
                $this->_send(
                    $templateName,
                    $senderName,
                    $senderEmail,
                    $recipientEmail,
                    $variables,
                    $storeId
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t send the email quote right now.'));
        }

        $this->inlineTranslation->resume();
        return true;
    }

    /**
     * Send Notification Email
     *
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string $recipientEmail
     * @param array $variables
     * @param int $storeId
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    protected function _send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $variables,
        $storeId
    ) {
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateName)
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($variables)
            ->setFrom([
                'name' => $senderName,
                'email' => $senderEmail
            ])
            ->addTo($recipientEmail)
            ->setReplyTo($senderEmail)
            ->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Get sub user or owner user email
     *
     * @param CartInterface $quote
     * @return array
     * @throws LocalizedException
     */
    public function getUser($quote)
    {
        $quoteId = $quote->getId();
        $customerEmail = $quote->getCustomerEmail();
        if ($this->helper->isEnableCompanyAccount($quote->getStore()->getWebsiteId())) {
            if ($quote->getIsAdminSubmitted()) {
                /** @var \Bss\QuoteExtension\Model\ManageQuote $manageQuote */
                $manageQuote = $this->quoteExtensionCollection->create()->addFieldToFilter("main_table.backend_quote_id", $quoteId)->getLastItem();
                if (!$manageQuote->getId()) {
                    $manageQuote = $this->quoteExtensionCollection->create()->addFieldToFilter("main_table.target_quote", $quoteId)->getLastItem();
                }
            } else {
                $manageQuote = $this->quoteExtensionCollection->create()->addFieldToFilter("main_table.quote_id", $quoteId)->getLastItem();
            }
            $subUserEmail = $manageQuote->getSubEmail();
            if ($subUserEmail) {
                return [
                    "name" => $manageQuote->getSubName(),
                    "email" => [
                        $subUserEmail,
                        $customerEmail
                    ]
                ];
            }
        }
        if ($quote->getCustomerId()) {
            return [
                "name" => $this->helper->getCustomerName($quote->getCustomerId()),
                "email" => $customerEmail
            ];
        }
        $this->setCustomerName($quote);
        return [
            "name" => $quote->getCustomerName(),
            "email" => $customerEmail
        ];
    }

    /**
     * Get Variables
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @param string $requestQuoteUrl
     * @return array
     * @throws Exception
     */
    public function getVariables($requestQuote, $quote, $requestQuoteUrl)
    {
        $this->quoteEmail->getQuoteById($quote->getEntityId());
        return [
            'increment_id' => $requestQuote->getIncrementId(),
            'created_at' => $this->emailData->getCreatedAtFormatted(
                $requestQuote->getCreatedAt(),
                $quote->getstore(),
                IntlDateFormatter::MEDIUM
            ),
            'request_url' => $requestQuoteUrl,
            'requestQuote' => $requestQuote,
            'quote' => $quote
        ];
    }

    /**
     * Get Quote id
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @return mixed
     */
    public function getQuoteId($requestQuote)
    {
        return $this->helper->getQuoteId($requestQuote);
    }

    /**
     * Check hide price when cron run
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @return mixed
     */
    public function checkHidePrice($requestQuote, $quote)
    {
        if ($requestQuote->getStatus() === Status::STATE_PENDING
            || $requestQuote->getStatus() === Status::STATE_CANCELED
            || $requestQuote->getStatus() === Status::STATE_REJECTED
        ) {
            foreach ($quote->getAllVisibleItems() as $item) {
                /* @var $item Item */
                $product = $item->getProduct();
                $item->setNeedCheckPrice(true);
                $item->setProduct($product);
                if ($item->getProductType() == 'configurable') {
                    $parentProductId = $item->getProductId();
                    $childProductSku = $item->getSku();
                    $canShowPrice = $this->hidePriceEmail->canShowPrice($parentProductId, $childProductSku);
                } else {
                    $canShowPrice = $this->hidePriceEmail->canShowPrice($item->getProductId(), false);
                }
                if (!$canShowPrice) {
                    $quote->setNeedHidePrice(true);
                    break;
                }
            }
        } else {
            $quote->setNeedHidePrice(false);
        }
        return $quote;
    }

    /**
     * Set customer name for quote
     * @param \Magento\Quote\Model\Quote|CartInterface $quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function setCustomerName($quote)
    {
        $customerName = '';
        if ($quote->getCustomerPrefix()) {
            $customerName .= $quote->getCustomerPrefix() . ' ';
        }
        $customerName .= $quote->getCustomerFirstname();
        if ($quote->getCustomerMiddlename()) {
            $customerName .= ' ' . $quote->getCustomerMiddlename();
        }
        $customerName .= ' ' . $quote->getCustomerLastname();
        if ($quote->getCustomerSuffix()) {
            $customerName .= ' ' . $quote->getCustomerSuffix();
        }

        return $quote->setCustomerName($customerName);
    }

    /**
     * Get name customer by quote
     *
     * @param \Bss\QuoteExtension\Model\Quote $quote
     * @return string|null
     */
    public function getCustomerNameByQuote($quote)
    {
        $name = $this->helper->getCustomerName($quote->getCustomerId());
        if (!$name) {
            $name = $quote->getCustomerName();
        }
        return $name;
    }
}
