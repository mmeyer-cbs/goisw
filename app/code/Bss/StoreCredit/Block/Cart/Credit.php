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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Block\Cart;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\Framework\View\Element\Template\Context;
use Bss\StoreCredit\Helper\Data as StoreCreditData;
use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;

/**
 * Class Credit
 */
class Credit extends Template
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSession;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    private $storeCreditRepository;

    /**
     * Credit constructor.
     * @param \Bss\StoreCredit\Model\Currency $currency
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param StoreCreditData $bssStoreCreditHelper
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param array $data
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        Context $context,
        CheckoutSession $checkoutSession,
        StoreCreditData $bssStoreCreditHelper,
        StoreCreditRepositoryInterface $storeCreditRepository,
        array $data = []
    ) {
        $this->currency = $currency;
        parent::__construct($context, $data);
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->storeCreditRepository = $storeCreditRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get store credit customer login
     *
     * @return string|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCreditTotal()
    {
        $quote = $this->checkoutSession->create()->getQuote();
        $amountUsedStoreView = $this->currency->convertAmountInput($quote)[1];
        $credit = $this->storeCreditRepository->get();
        if ($credit->getId()) {
            $creditCurrencyCode = $this->currency->getCreditCurrencyCode($credit->getCurrencyCode(), $quote->getStore()->getWebsiteId());
            $amountLeft = $this->currency->convertCurrency($credit->getBalanceAmount(), $creditCurrencyCode, $quote->getQuoteCurrencyCode()) - $amountUsedStoreView;
            return $this->currency->formatPrice($amountLeft, $quote->getQuoteCurrencyCode());
        }
        return false;
    }

    /**
     * Get store credit customer used
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCreditUsed()
    {
        $quote = $this->checkoutSession->create()->getQuote();
        $balanceUsed = $this->currency->convertAmountInput($quote)[1];
        if ($quote->getId() && $balanceUsed) {
            return $this->currency->round($balanceUsed);
        }
        return 0;
    }

    /**
     * Is display store credit in shopping cart
     *
     * @return bool
     */
    public function isDisplay()
    {
        return $this->bssStoreCreditHelper->getGeneralConfig('cart_page_display');
    }
}
