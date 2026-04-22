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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Observer;

use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Bss\StoreCredit\Helper\Data;
use Bss\StoreCredit\Model\History;
use Bss\StoreCredit\Model\HistoryFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SalesEventQuoteSubmitSuccessObserver
 * @package Bss\StoreCredit\Observer
 */
class SalesEventQuoteSubmitSuccessObserver implements ObserverInterface
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Bss\StoreCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    private $storeCreditRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param Data $bssStoreCreditHelper
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        StoreManagerInterface $storeManager,
        StoreCreditRepositoryInterface $storeCreditRepository,
        Data $bssStoreCreditHelper,
        HistoryFactory $historyFactory
    ) {
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->storeCreditRepository = $storeCreditRepository;
        $this->historyFactory = $historyFactory;
    }

    /**
     * Handle data store credit
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->bssStoreCreditHelper->getGeneralConfig('active')) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $convertAmountInput = $this->currency->convertAmountInput($quote);
        $baseAmount =  $convertAmountInput[0];
        $amount = $convertAmountInput[1];
        $websiteId = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();
        $customerId = $order->getCustomerId();
        if ($baseAmount && $amount && $customerId) {
            $credit = $this->storeCreditRepository->get($customerId, $websiteId);
            $historyModel = $this->historyFactory->create();
            $order->setBssStorecreditAmount($amount)
                ->setBaseBssStorecreditAmount($baseAmount)
                ->save();
            $creditCurrencyCode = $this->currency->getCreditCurrencyCode($credit->getCurrencyCode(), $websiteId);
            $amountAfter = $credit->getBalanceAmount() - $this->currency->convertCurrency($amount, $order->getOrderCurrencyCode(), $creditCurrencyCode);
            $credit->setBalanceAmount($amountAfter)->save();
            $data = [
                'customer_id' => $customerId,
                'order_id' => $order->getId(),
                'website_id' => $websiteId,
                'type' => History::TYPE_USED_IN_ORDER,
                'change_amount' => -$this->currency->convertCurrency($baseAmount, $order->getBaseCurrencyCode(), $creditCurrencyCode),
                'balance_amount' => $amountAfter,
                'comment_content' => null,
                'is_notified' => true,
                'currency_code' => $order->getOrderCurrencyCode(),
                'credit_currency_code' => $creditCurrencyCode,
                'change_amount_store_view' => -$amount,
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail()
            ];
            $historyModel->updateHistory($data, $order->getStoreId());
        }
    }
}
