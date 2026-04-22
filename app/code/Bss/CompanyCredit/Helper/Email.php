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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyCredit\Helper;

use Bss\CompanyCredit\Helper\Data as HelperData;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Mail.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Email extends AbstractHelper
{
    const PATH_COMPANYCREDIT_EMAIL_UPDATE_CREDIT_VALUE = "companycredit/email/update_credit_value";
    const PATH_COMPANYCREDIT_EMAIL_CHANGE_CREDIT_LIMIT = "companycredit/email/change_credit_limit";
    const PATH_COMPANYCREDIT_EMAIL_CREDIT_LIMIT_EXCEED = "companycredit/email/credit_limit_exceed";
    const PATH_COMPANYCREDIT_EMAIL_DUE_DATE_PAYMENT_REMINDER = "companycredit/email/due_date_payment_reminder";
    const PATH_COMPANYCREDIT_EMAIL_NOTIFICATION_OVERDUE = "companycredit/email/notification_overdue";
    const PATH_COMPANYCREDIT_EMAIL_SEND_MAIL_BEFORE_OVERDUE = 'companycredit/email/send_mail_before_overdue';
    const PATH_COMPANYCREDIT_EMAIL_SENDER = "companycredit/email/sender";
    const PATH_COMPANYCREDIT_EMAIL_RECEIVER = "companycredit/email/receiver";

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface $storeManagerInterface
     */
    protected $storeManager;

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
     * Email constructor.
     * @param Data $helperData
     * @param Context $context
     * @param StoreManagerInterface $storeManagerInterface
     * @param StateInterface $inlineTranslation
     * @param ManagerInterface $messageManager
     * @param TransportBuilder $transportBuilder
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        HelperData $helperData,
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        StateInterface $inlineTranslation,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        SenderResolverInterface $senderResolver
    ) {
        $this->helperData = $helperData;
        parent::__construct($context);
        $this->storeManager = $storeManagerInterface;
        $this->inlineTranslation = $inlineTranslation;
        $this->messageManager = $messageManager;
        $this->transportBuilder = $transportBuilder;
        $this->senderResolver = $senderResolver;
    }

    /**
     * Get Sender Email
     *
     * @param int|mixed $storeId
     * @return mixed
     * @throws MailException
     */
    public function getEmailSender($storeId = null)
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get Sender Name
     *
     * @param int|mixed $storeId
     * @return mixed
     * @throws MailException
     */
    public function getEmailSenderName($storeId = null)
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }

    /**
     * Get email for change credit limit
     *
     * @param int|mixed $storeId
     * @return mixed
     */
    public function getEmailChangeCreditLimit($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_CHANGE_CREDIT_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for credit limit exceed
     *
     * @param int|mixed $storeId
     * @return mixed
     */
    public function getEmailCreditLimitExceed($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_CREDIT_LIMIT_EXCEED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for credit limit exceed
     *
     * @param int|mixed $storeId
     * @return mixed
     */
    public function getEmailUpdateCreditValue($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_UPDATE_CREDIT_VALUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for Due Date Payment Reminder
     *
     * @param int|mixed $storeId
     * @return mixed
     */
    public function getEmailDueDatePaymentReminder($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_DUE_DATE_PAYMENT_REMINDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for Notification Overdue
     *
     * @param int|mixed $storeId
     * @return mixed
     */
    public function getEmailNotificationOverdue($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_NOTIFICATION_OVERDUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get X day(s) before overdue
     *
     * @param int|mixed $storeId
     * @return mixed
     */
    public function getDaySendMailBeforeOverdue($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_SEND_MAIL_BEFORE_OVERDUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email for receive
     *
     * @param int|mixed $storeId
     * @return mixed
     * @throws MailException
     */
    public function getEmailReceiveEmail($storeId = null)
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_COMPANYCREDIT_EMAIL_RECEIVER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Send email for customer when admin change credit limit or update credit value
     *
     * @param array $data
     * @param string $type
     * @throws MailException
     * @throws LocalizedException
     */
    public function sendEmailAdmin($data, $type)
    {
        if ($this->helperData->isEnableModule($data["website_id"])) {
            if ($type == "sendChangeCreditLimit") {
                $templateName = $this->getEmailChangeCreditLimit($data["store_id"]);
            }
            if ($type == 'sendUpdateCreditValue') {
                $templateName = $this->getEmailUpdateCreditValue($data["store_id"]);
            }
            if ($type == "sendDueDatePaymentReminder") {
                $templateName = $this->getEmailDueDatePaymentReminder($data["store_id"]);
            }
            if ($type == "sendNotificationOverdue") {
                $templateName = $this->getEmailNotificationOverdue($data["store_id"]);
            }

            $senderEmail = $this->getEmailSender($data["store_id"]);
            if ($senderEmail && !empty($templateName)) {
                $senderName = $this->getEmailSenderName($data["store_id"]);
                $variables = $data["variables"];
                $recipientEmail = $data["customer_email"];
                $this->send(
                    $templateName,
                    $senderName,
                    $senderEmail,
                    $recipientEmail,
                    $variables,
                    $data["store_id"]
                );
            }
        }
    }

    /**
     * Send email for admin when customer exceed credit limit
     *
     * @param array $variables
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendCreditLimitExceed($variables)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $templateName = $this->getEmailCreditLimitExceed($storeId);
        $senderEmail = $this->getEmailSender($storeId);
        if ($senderEmail) {
            $senderName = $this->getEmailSenderName($storeId);
            $recipientEmail = $this->getEmailReceiveEmail($storeId);
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
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t send the company credit email right now.'));
        }
        return true;
    }
}
