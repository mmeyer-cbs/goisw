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
namespace Bss\QuoteExtension\Block;

use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 100.0.2
 */
class Registration extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\QuoteExtension\Model\QuoteSuccess
     */
    protected $quoteSuccess;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param \Bss\QuoteExtension\Model\QuoteSuccess $quoteSuccess
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Bss\QuoteExtension\Model\QuoteSuccess $quoteSuccess,
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        array $data = []
    ) {
        $this->quoteSuccess = $quoteSuccess;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->registration = $registration;
        $this->accountManagement = $accountManagement;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current email address
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEmailAddress()
    {
        return $this->quoteSuccess->getLastQuote()->getCustomerEmail();
    }

    /**
     * Retrieve account creation url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCreateAccountUrl()
    {
        return $this->getUrl('quoteextension/account/delegateCreate');
    }

    /**
     * Display show create account when customer not login, not account has email
     */
    public function toHtml()
    {
        if ($this->customerSession->isLoggedIn()
            || !$this->registration->isAllowed()
            || !$this->accountManagement->isEmailAvailable($this->getEmailAddress())
        ) {
            return '';
        }
        return parent::toHtml();
    }

}
