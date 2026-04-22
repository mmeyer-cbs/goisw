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
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Paymentshipping\Helper;

class HelperPaymentShip
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Backend\Model\Session\QuoteFactory
     */
    protected $backendQuote;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shipConfig;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    protected $storeManager = null;

    /**
     * HelperPaymentShip constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Backend\Model\Session\QuoteFactory $backendQuote
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Shipping\Model\Config $shipConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Backend\Model\Session\QuoteFactory $backendQuote,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Shipping\Model\Config $shipConfig
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->paymentHelper = $paymentHelper;
        $this->backendQuote = $backendQuote;
        $this->customer = $customer;
        $this->quoteRepository = $quoteRepository;
        $this->httpContext = $httpContext;
        $this->groupFactory = $groupFactory;
        $this->shipConfig = $shipConfig;
    }

    /**
     * @return \Magento\Shipping\Model\Config
     */
    public function returnShippingConfig()
    {
        return $this->shipConfig;
    }

    /**
     * @return \Magento\Customer\Model\GroupFactory
     */
    public function returnGroupFactory()
    {
        return $this->groupFactory;
    }

    /**
     * @return \Magento\Framework\App\Http\Context
     */
    public function returnHttpContext()
    {
        return $this->httpContext;
    }

    /**
     * @return \Magento\Customer\Model\SessionFactory
     */
    public function returnCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Magento\Payment\Helper\Data
     */
    public function returnPaymentHelper()
    {
        return $this->paymentHelper;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface|null
     */
    public function returnStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Quote\Api\CartRepositoryInterface
     */
    public function returnQuoteRepository()
    {
        return $this->quoteRepository;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function returnModelCustomer()
    {
        return $this->customer;
    }

    /**
     * @return \Magento\Backend\Model\Session\QuoteFactory
     */
    public function returnBackendQuote()
    {
        return $this->backendQuote;
    }
}
