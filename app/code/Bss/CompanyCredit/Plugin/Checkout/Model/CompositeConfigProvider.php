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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Plugin\Checkout\Model;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Bss\CompanyCredit\Helper\Data as HelperData;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class CompositeConfigProvider Bss.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var HelperCurrency
     */
    protected $helperCurrency;

    /**
     * @var null|\Magento\Quote\Api\Data\CartInterface
     */
    protected $quote = null;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CreditRepositoryInterface
     */
    protected $creditRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * CompositeConfigProvider constructor.
     *
     * @param HelperCurrency $helperCurrency
     * @param CartRepositoryInterface $quoteRepository
     * @param HelperData $helperData
     * @param CreditRepositoryInterface $creditRepository
     * @param Session $customerSession
     */
    public function __construct(
        HelperCurrency $helperCurrency,
        CartRepositoryInterface $quoteRepository,
        HelperData $helperData,
        CreditRepositoryInterface $creditRepository,
        Session $customerSession
    ) {
        $this->helperCurrency = $helperCurrency;
        $this->quoteRepository = $quoteRepository;
        $this->helperData = $helperData;
        $this->creditRepository = $creditRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Pass data window.checkoutConfig
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig()
    {
        $enableModule = $this->helperData->isEnableModule();
        $config = [];
        if ($enableModule) {
            $customerId = $this->getSessionCustomerId();
            if ($customerId) {
                $credit = $this->creditRepository->get($customerId);
                if ($credit && $credit->getId()) {
                    $currencyCodeCredit = $credit->getCurrencyCode();
                    $availableCredit = $credit->getAvailableCredit();
                    $currencyCodeQuote = $this->getQuote()->getBaseCurrencyCode();
                    $availableCredit =
                        $this->helperCurrency->convertCurrency($availableCredit, $currencyCodeCredit, $currencyCodeQuote);
                    $config['companyCredit']["enableModule"] = 1;
                    $availableCreditCurrency =
                        $this->displayAvailableCredit($credit->getAvailableCredit(), $currencyCodeCredit);

                    $config['companyCredit']["availableCredit"] = $availableCredit;
                    $config['companyCredit']["availableCreditCurrency"] = $availableCreditCurrency;
                    $config['companyCredit']["allowExceed"] = (float)($credit->getAllowExceed());
                }
            }
        }
        return $config;
    }

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    private function getSessionCustomerId()
    {
        if ($this->customerSession->getCustomerId()) {
            return $this->customerSession->getCustomerId();
        }
        return 0;
    }

    /**
     * Get quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuote()
    {
        if (!$this->quote) {
            try {
                $this->quote = $this->quoteRepository->getActiveForCustomer($this->getSessionCustomerId());
            } catch (\Exception $exception) {
                $this->helperData->logError($exception->getMessage());
                return null;
            }
        }
        return $this->quote;
    }

    /**
     * Display available credit by store view
     *
     * @param string $availableCredit
     * @param string $currencyCode
     * @return string
     */
    public function displayAvailableCredit($availableCredit, $currencyCode)
    {
        $currencySymbol = $this->helperCurrency->getCurrencyCodeStoreView();
        $availableCredit = $this->helperCurrency->convertCurrency($availableCredit, $currencyCode, $currencySymbol);
        return $this->helperCurrency->formatPrice($availableCredit, $currencySymbol);
    }
}
