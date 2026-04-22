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
namespace Bss\StoreCredit\Block\Adminhtml\Order\Create;

use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Store\Model\StoreFactory;
use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;

/**
 * Class Credit
 */
class Credit extends AbstractCreate
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    private $storeCreditRepository;

    /**
     * @param \Bss\StoreCredit\Model\Currency $currency
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreFactory $storeFactory
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param array $data
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        StoreFactory $storeFactory,
        StoreCreditRepositoryInterface $storeCreditRepository,
        array $data = []
    ) {
        $this->currency = $currency;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->storeFactory = $storeFactory;
        $this->storeCreditRepository = $storeCreditRepository;
    }

    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_store_credit');
    }

    /**
     * Get store credit customer total
     *
     * @return bool|float|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreCreditTotal()
    {
        $quote = $this->getQuote();
        $store = $this->storeFactory->create()->load($quote->getStoreId());
        $credit = $this->storeCreditRepository->get($quote->getCustomerId(), $store->getWebsiteId());
        if ($credit->getId()) {
            $creditCurrencyCode = $this->currency->getCreditCurrencyCode($credit->getCurrencyCode(), $store->getWebsiteId());
            $amountUsed = $this->getQuote()->getBssStorecreditAmount();
            $amountLeft = $this->currency->convertCurrency($credit->getBalanceAmount(), $creditCurrencyCode, $quote->getQuoteCurrencyCode()) - $amountUsed;
            return $this->currency->formatPrice($amountLeft, $quote->getQuoteCurrencyCode());
        }
        return false;
    }

    /**
     * Get store credit customer used
     *
     * @return float|bool
     */
    public function getStoreCreditUsed()
    {
        $quote = $this->getQuote();
        $amount = $quote->getBssStorecreditAmount();
        if ($quote->getId() && $amount > 0) {
            return $this->currency->formatPrice($amount, $quote->getQuoteCurrencyCode());
        }
        return false;
    }
}
