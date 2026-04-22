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

namespace Bss\CompanyAccount\Plugin\Paypal\Braintree\Model;

use Magento\Framework\App\Request\Http;
use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Helper\QuoteHelper;
use Bss\CompanyAccount\Helper\Data;
use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use PayPal\Braintree\Model\StoreConfigResolver as CoreStoreConfig;
use Psr\Log\LoggerInterface;

/**
 * Class StoreConfigResolver
 *
 * @package Bss\CompanyAccount\Plugin\Paypal
 */
class StoreConfigResolver
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SessionQuote
     */
    protected $sessionQuote;

    /**
     * @var RequestHttp
     */
    protected $request;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RequestHttp
     */
    protected $requestHttp;

    /**
     * Function construct
     *
     * @param RequestHttp $requestHttp
     * @param StoreManagerInterface $storeManager
     * @param SessionQuote $sessionQuote
     * @param RequestHttp $request
     * @param QuoteFactory $quoteFactory
     * @param QuoteHelper $quoteHelper
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        Http $requestHttp,
        StoreManagerInterface   $storeManager,
        SessionQuote            $sessionQuote,
        RequestHttp             $request,
        QuoteFactory            $quoteFactory,
        QuoteHelper             $quoteHelper,
        LoggerInterface         $logger,
        Data                    $helper
    ) {
        $this->requestHttp=$requestHttp;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->sessionQuote = $sessionQuote;
        $this->quoteFactory = $quoteFactory;
        $this->quoteHelper = $quoteHelper;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Function around get store id
     *
     * @param CoreStoreConfig $subject
     * @param callable $proceed
     * @return callable|int
     */
    public function aroundGetStoreId($subject, callable $proceed)
    {
        if ($this->helper->isCompanyAccount()) {
            $customerId = $this->quoteHelper->getCustomerSession()->getCustomerId();
            ($subUser = $this->quoteHelper->getCustomerSession()->getSubUser())
                ? $quoteId = $this->quoteHelper->checkQuote($subUser->getId(), SubUserQuoteInterface::SUB_USER_ID)
                : $quoteId = $this->quoteHelper->checkQuote($customerId, SubUserQuoteInterface::CUSTOMER_ID);
            if ($quoteId || $this->requestHttp->getFullActionName() == "bss_companyAccount_order_checkout") {
                $currentStoreId = null;
                $currentStoreIdInAdmin = $this->sessionQuote->getStoreId();
                if (!$currentStoreIdInAdmin) {
                    try {
                        $currentStoreId = $this->storeManager->getStore()->getId();
                    } catch (NoSuchEntityException $e) {
                        $this->logger->critical($e);
                    }
                }
                $dataParams = $this->request->getParams();
                if (isset($dataParams['order_id'])) {
                    $quote = $this->quoteFactory->create()->load($dataParams['order_id']);
                    if ($quote->getEntityId()) {
                        return $quote->getStoreId();
                    }
                }
                return $currentStoreId ?: $currentStoreIdInAdmin;
            }
        }
        return $proceed();
    }
}
