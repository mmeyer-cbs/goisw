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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
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
 * Custom Module Email helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mail extends AbstractHelper
{
    const PATH_SALES_REP_EMAIL_IDENTITY = 'bss_salesrep/salesrep_email_config/sender_email_identity';
    const PATH_SALES_REP_EMAIL_COPY = 'bss_salesrep/salesrep_email_config/send_email_copy';
    const PATH_SALES_REP_EMAIL_ASSIGN = 'bss_salesrep/salesrep_email_config/assign_salesrep';
    const PATH_SALES_REP_EMAIL_UNASSIGN = 'bss_salesrep/salesrep_email_config/unassign_salesrep';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Mail constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param SenderResolverInterface $senderResolver
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        SenderResolverInterface $senderResolver,
        \Bss\SalesRep\Helper\Data $helper,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder
    ) {
        $this->scopeConfig = $context;
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->senderResolver = $senderResolver;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * Get Sender Email
     *
     * @return mixed
     * @throws MailException
     */
    public function getEmailSender()
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_SALES_REP_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get Sender Name
     *
     * @return mixed
     * @throws MailException
     */
    public function getEmailSenderName()
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_SALES_REP_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }

    /**
     * Get Email copy to
     *
     * @return array
     */
    public function getEmailCoppy()
    {
        $sendEmailCoppys = $this->scopeConfig->getValue(
            self::PATH_SALES_REP_EMAIL_COPY,
            ScopeInterface::SCOPE_STORE
        );
        if ($sendEmailCoppys != '') {
            return $this->helper->toArray($sendEmailCoppys);
        }
        return [];
    }

    /**
     * Email Assign Sales Rep
     *
     * @return mixed
     */
    public function getEmailAssignSalesRep()
    {
        return $this->scopeConfig->getValue(
            self::PATH_SALES_REP_EMAIL_ASSIGN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Email Unassign Sales Rep
     *
     * @return mixed
     */
    public function getEmailUnassignSalesRep()
    {
        return $this->scopeConfig->getValue(
            self::PATH_SALES_REP_EMAIL_UNASSIGN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Return template id according to store
     *
     * @param string $xmlPath
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Send Mail
     *
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param array $emailCoppys
     * @param string $recipientEmail
     * @param array $variables
     * @param int $storeId
     * @return bool
     */
    public function send(
        $templateName,
        $senderName,
        $senderEmail,
        $emailCoppys,
        $recipientEmail,
        $variables,
        $storeId
    ) {
        $this->inlineTranslation->suspend();
        try {
            $this->_send(
                $templateName,
                $senderName,
                $senderEmail,
                $emailCoppys,
                $recipientEmail,
                $variables,
                $storeId
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t send the email.'));
        }

        $this->inlineTranslation->resume();
        return true;
    }

    /**
     * Send Mail
     *
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param array $emailCoppys
     * @param string $recipientEmail
     * @param array $variables
     * @param int $storeId
     * @return void
     */
    protected function _send(
        $templateName,
        $senderName,
        $senderEmail,
        $emailCoppys,
        $recipientEmail,
        $variables,
        $storeId
    ) {
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateName)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars((array)$variables)
                ->setFromByScope(
                    [
                        'name' => $senderName,
                        'email' => $senderEmail
                    ]
                );
            foreach ($emailCoppys as $emailCoppy) {
                $transport->addCc($emailCoppy);
            }

            $transport->addTo($recipientEmail)
                ->setReplyTo($senderEmail)
                ->getTransport()->sendMessage();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send mail Assign Sales Rep
     *
     * @param string $recipientEmail
     * @param array $variables
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmailAssignSalesRep($recipientEmail, $variables)
    {
        $templateAssign = $this->getEmailAssignSalesRep();
        $senderName = $this->getEmailSenderName();
        $senderEmail = $this->getEmailSender();
        $emailCopys = $this->getEmailCoppy();
        $storeId = $this->getStore();
        $this->send(
            $templateAssign,
            $senderName,
            $senderEmail,
            $emailCopys,
            $recipientEmail,
            $variables,
            (int)$storeId
        );
    }

    /**
     * Send mail Unassigned Sales Rep
     *
     * @param string $recipientEmail
     * @param array $variables
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmailUnassignSalesRep($recipientEmail, $variables)
    {
        $templateAssign = $this->getEmailUnassignSalesRep();
        $senderName = $this->getEmailSenderName();
        $senderEmail = $this->getEmailSender();
        $emailCopys = $this->getEmailCoppy();
        $storeId = $this->getStore();
        $this->send(
            $templateAssign,
            $senderName,
            $senderEmail,
            $emailCopys,
            $recipientEmail,
            $variables,
            (int)$storeId
        );
    }
}
