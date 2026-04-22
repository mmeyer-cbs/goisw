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
namespace Bss\StoreCredit\Model;

use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Bss\StoreCredit\Helper\Data as StoreCreditData;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;

/**
 * Class CompositeConfigProvider
 */
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Currency
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
     * @param Currency $currency
     * @param StoreCreditData $bssStoreCreditHelper
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        StoreCreditData $bssStoreCreditHelper,
        StoreCreditRepositoryInterface $storeCreditRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->currency = $currency;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeCreditRepository = $storeCreditRepository;
    }

    /**
     * Add data store credit in checkout page
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $output = [];
        if ($this->bssStoreCreditHelper->getGeneralConfig('checkout_page_display')) {
            $credit = $this->storeCreditRepository->get();
            $quote = $this->checkoutSession->create()->getQuote();
            $balanceUsed = $this->currency->convertAmountInput($quote)[1];

            $output['storeCreditQuote'] = '';
            $output['storeCreditTotal'] = '';
            if ($credit->getId()) {
                $amountLeft = $this->currency->convertCurrency($credit->getBalanceAmount(), $credit->getCurrenyCode(), $quote->getQuoteCurrencyCode()) - $balanceUsed;
                $output['storeCreditQuote'] = $this->currency->round($balanceUsed);
                $output['storeCreditTotal'] =  $this->currency->formatPrice($amountLeft, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }
}
