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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Helper;

use Bss\B2bRegistration\Helper\Email as BssHelperEmail;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\UrlFactory;
use Psr\Log\LoggerInterface;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;
use Magento\Customer\Helper\Address;

class CreatePostHelper
{
    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Email
     */
    protected $helperEmail;

    /**
     * CreatePostHelper constructor.
     * @param AccountManagementInterface $accountManagement
     * @param UrlFactory $urlFactory
     * @param Validator $formKeyValidator
     * @param LoggerInterface $logger
     * @param AccountRedirect $accountRedirect
     * @param Email $helperEmail
     * @param CreateAccount $helperCreateAccount
     */
    public function __construct(
        AccountManagementInterface $accountManagement,
        UrlFactory $urlFactory,
        Validator $formKeyValidator,
        LoggerInterface $logger,
        AccountRedirect $accountRedirect,
        BssHelperEmail $helperEmail
    ) {
        $this->accountManagement = $accountManagement;
        $this->urlFactory = $urlFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
        $this->accountRedirect = $accountRedirect;
        $this->helperEmail = $helperEmail;
    }

    /**
     * @return string
     */
    public function returnTypeShipping()
    {
        return Address::TYPE_SHIPPING;
    }

    /**
     * @return AccountManagementInterface
     */
    public function returnAccountManagement()
    {
        return $this->accountManagement;
    }

    /**
     * @return Validator
     */
    public function returnValidator()
    {
        return $this->formKeyValidator;
    }

    /**
     * @return LoggerInterface
     */
    public function returnLogger()
    {
        return $this->logger;
    }

    /**
     * @return AccountRedirect
     */
    public function returnAccountRedirect()
    {
        return $this->accountRedirect;
    }

    /**
     * @return Email
     */
    public function returnBssHelperEmail()
    {
        return $this->helperEmail;
    }

    /**
     * @return UrlFactory
     */
    public function returnUrlFactory()
    {
        return $this->urlFactory;
    }

    /**
     * @return string
     */
    public function returnConfirmRequire()
    {
        return AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED;
    }

    /**
     * @return int
     */
    public function returnApproval()
    {
        return CustomerAttribute::B2B_APPROVAL;
    }

    /**
     * @return int
     */
    public function returnPending()
    {
        return CustomerAttribute::B2B_PENDING;
    }
}
