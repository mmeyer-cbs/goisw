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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Paymentshipping\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Integration\Model\Oauth\TokenFactory;

/**
 * Class Api
 * @package Bss\Paymentshipping\Helper
 */
class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * Api constructor.
     * @param TokenFactory $tokenFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Context $context
     */
    public function __construct(
        TokenFactory $tokenFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Context $context
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * Get shipping title
     *
     * @param string $shippingCode
     * @param int $storeViewId
     * @return string
     */
    public function getShippingTitle($shippingCode, $storeViewId)
    {
        $title = $this->scopeConfig->getValue(
            'carriers/' . $shippingCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeViewId
        );
        if ($title) {
            return $title;
        }
        return $shippingCode;
    }

    /**
     * Get payment title
     *
     * @param string $paymentCode
     * @param int $storeViewId
     * @return string
     */
    public function getPaymentTitle($paymentCode, $storeViewId)
    {
        $title = $this->scopeConfig->getValue(
            'payment/' . $paymentCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeViewId
        );
        if ($title) {
            return $title;
        }
        return $paymentCode;
    }

    /**
     * Get title shipping or payment
     *
     * @param string $type
     * @param string $method
     * @param int|null $storeViewId
     * @return mixed
     */
    public function getTitle($type, $method, $storeViewId = null)
    {
        if ($type == "shipping") {
            return $this->getShippingTitle($method, $storeViewId);
        }
        return $this->getPaymentTitle($method, $storeViewId);
    }

    /**
     * Get customer group id by token customer
     *
     * @param string $token
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerGroupId($token)
    {
        $customerGroupId = 0;
        if ($token) {
            $oauthToken = $this->tokenFactory->create()->loadByToken($token);
            if ($customerId = $oauthToken->getCustomerId() && !$oauthToken->getRevoked()) {
                $customer = $this->customerRepositoryInterface->getById($customerId);
                $customerGroupId = (int) $customer->getGroupId();
            }
        }
        return $customerGroupId;
    }
}
