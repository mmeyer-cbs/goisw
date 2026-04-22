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
namespace Bss\StoreCredit\Model\ResourceModel;

use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Bss\StoreCredit\Helper\Data as StoreCreditData;
use Bss\StoreCredit\Model\CreditFactory;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class StoreCreditRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreCreditRepository implements StoreCreditRepositoryInterface
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;
    /**
     * @var Credit\Collection
     */
    protected $creditCollection;
    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @var \Bss\StoreCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;

    /**
     * @var array
     */
    private $storeCreditRegistryByCustomer = [];

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @param \Bss\StoreCredit\Model\Currency $currency
     * @param CollectionProcessor $collectionProcessor
     * @param Credit\CollectionFactory $creditCollection
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param StoreCreditData $bssStoreCreditHelper
     * @param CreditFactory $creditFactory
     * @param SessionFactory $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Data $jsonHelper
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        CollectionProcessor $collectionProcessor,
        \Bss\StoreCredit\Model\ResourceModel\Credit\CollectionFactory $creditCollection,
        SearchResultsInterfaceFactory $searchResultsFactory,
        StoreCreditData $bssStoreCreditHelper,
        CreditFactory $creditFactory,
        SessionFactory $customerSession,
        CheckoutSession $checkoutSession,
        Data $jsonHelper,
        QuoteRepository $quoteRepository
    ) {
        $this->currency = $currency;
        $this->collectionProcessor = $collectionProcessor;
        $this->creditCollection = $creditCollection;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->creditFactory = $creditFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->jsonHelper = $jsonHelper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Apply store credit for quote
     *
     * @param float $amount
     * @return StoreCreditRepositoryInterface|string|void
     */
    public function apply($amount)
    {
        if ($this->bssStoreCreditHelper->getGeneralConfig('checkout_page_display')) {
            $response = [];
            $amount = $this->currency->round($amount);

            $quote = $this->checkoutSession->create()->getQuote();
            $baseAmount = $amount;
            $message = $this->bssStoreCreditHelper->getSuccessApplyCreditMsg();

            $creditModel = $this->creditFactory->create();
            $totals_amount = $this->getTotalsAmount($quote);
            if ($amount < 0 || !$quote->getId() || !$creditModel->validateBalance($quote, $baseAmount)) {
                $response['status'] = false;
                $response['message'] = __('Something went wrong. Please enter a value again');
            } elseif ($baseAmount > $totals_amount) {
                $this->saveStoreCreditApplyToQuote($quote, $totals_amount, $message, $response);
            } else {
                $this->saveStoreCreditApplyToQuote($quote, $baseAmount, $message, $response);
            }
            return $this->jsonHelper->jsonEncode($response);
        }
    }

    /**
     * Get total amount by base currency
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    private function getTotalsAmount($quote)
    {
        $totals_amount = $quote->getData('subtotal_with_discount');
        $totals = $quote->getTotals();
        $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
        foreach ($totals as $code => $total) {
            if ($this->bssStoreCreditHelper->getGeneralConfig('used_tax') && $code == 'tax') {
                $totals_amount += $total->getValue();
            }
        }
        if ($this->bssStoreCreditHelper->getGeneralConfig('used_shipping')) {
            $totals_amount += $shippingAmount;
        }
        $convert_to_base_currency = $this->currency->convertCurrency($totals_amount, $quote->getQuoteCurrencyCode(), $quote->getBaseCurrencyCode());
        return $this->currency->round($convert_to_base_currency);
    }

    /**
     * Get store credit by customer id, website id
     *
     * @param int|null $customerId
     * @param int|null $websiteId
     * @return StoreCreditRepositoryInterface|\Bss\StoreCredit\Model\Credit|mixed
     * @throws NoSuchEntityException|LocalizedException
     */
    public function get($customerId = null, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->bssStoreCreditHelper->getWebsiteIdbyStore();
        }
        if ($customerId === null) {
            $customerId = (int) $this->customerSession->create()->getCustomer()->getId();
        }

        if (isset($this->storeCreditRegistryByCustomer[$customerId])) {
            return $this->storeCreditRegistryByCustomer[$customerId];
        }

        $creditModel = $this->creditFactory->create();

        if (isset($websiteId)) {
            $creditModel->setWebsiteId($websiteId);
        }

        $credit = $creditModel->loadByCustomer($customerId);
        return $credit;
    }

    /**
     * Get list store credit
     *
     * @param SearchCriteriaInterface $criteria
     * @return \Bss\StoreCredit\Api\StoreCreditSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->creditCollection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Save storecredit apply to quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param float $storeCredit
     * @param string $message
     * @param array $response
     * @return void
     */
    public function saveStoreCreditApplyToQuote($quote, $storeCredit, $message, &$response)
    {
        $quote->setBaseBssStorecreditAmountInput($storeCredit);
        $quote->setBssStorecreditAmountInput($storeCredit);
        $quote->setStoreCreditCurrencyCode($quote->getQuoteCurrencyCode());
        $quote->collectTotals();
        try {
            $this->quoteRepository->save($quote);
            $credit = $this->get();

            $creditCurrencyCode = $this->currency->getCreditCurrencyCode($credit->getCurrencyCode(), $quote->getStore()->getWebsiteId());
            $balanceAmount = $this->currency->convertCurrency($credit->getBalanceAmount(), $creditCurrencyCode, $quote->getQuoteCurrencyCode());
            $amountLeft = $balanceAmount - $storeCredit;
            $response['status'] = true;
            $response['message'] = $message;
            $response['amount'] = $storeCredit;
            $response['total'] = $this->currency->formatPrice($amountLeft, $quote->getQuoteCurrencyCode());
        } catch (InputException|CouldNotSaveException|NoSuchEntityException|LocalizedException $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
    }
}
