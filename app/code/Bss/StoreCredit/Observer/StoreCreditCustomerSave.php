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

use Magento\Framework\Event\ObserverInterface;
use Bss\StoreCredit\Model\CreditFactory;
use Bss\StoreCredit\Helper\Data;
use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Psr\Log\LoggerInterface;
use Bss\StoreCredit\Model\HistoryFactory;
use Magento\Framework\Event\Observer;
use Bss\StoreCredit\Model\History;

/**
 * Class StoreCreditCustomerSave
 * @package Bss\StoreCredit\Observer
 */
class StoreCreditCustomerSave implements ObserverInterface
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @var Bss\StoreCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    private $storeCreditRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Bss\StoreCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @param \Bss\StoreCredit\Model\Currency $currency
     * @param CreditFactory $creditFactory
     * @param Data $bssStoreCreditHelper
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param LoggerInterface $logger
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        CreditFactory $creditFactory,
        Data $bssStoreCreditHelper,
        StoreCreditRepositoryInterface $storeCreditRepository,
        LoggerInterface $logger,
        HistoryFactory $historyFactory
    ) {
        $this->currency = $currency;
        $this->creditFactory = $creditFactory;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->storeCreditRepository = $storeCreditRepository;
        $this->logger = $logger;
        $this->historyFactory = $historyFactory;
    }

    /**
     * Credit update for customer after save
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();
        $params = $observer->getRequest()->getParams();
        $customerId = $customer->getId();

        if (isset($params['bss_storecredit_balance']) && $customerId) {
            $storeCredit = $params['bss_storecredit_balance'];
            $websiteId = (int)  $storeCredit['website_id'];
            $amount = (float)  $storeCredit['amount_value'];
            $comment =  $storeCredit['comment_content'];
            $isNotified = (boolean)  $storeCredit['is_notify'];
            $storeId =  $storeCredit['store_id'];
            try {
                $credit = $this->storeCreditRepository->get($customerId, $websiteId);
                $currencyCode = $this->currency->getCreditCurrencyCode($credit->getCurrencyCredit(), $this->currency->getCurrencyCodeByWebsite($websiteId));
                $historyModel = $this->historyFactory->create();
                if ($credit->getBalanceId()) {
                    $amountAfter = $credit->getBalanceAmount() + $amount;
                    $credit->setBalanceAmount($amountAfter)
                        ->setCurrencyCode($currencyCode)
                        ->save();
                } else {
                    $amountAfter = $amount;
                    $this->creditFactory->create()
                        ->setBalanceAmount($amountAfter)
                        ->setWebsiteId($websiteId)
                        ->setCustomerId($customerId)
                        ->setCurrencyCode($currencyCode)
                        ->save();
                }
                $customerName = $customer->getFirstname().' '.$customer-> getMiddlename().' '.$customer->getLastname();
                $data = [
                    'customer_id' => $customerId,
                    'website_id' => $websiteId,
                    'type' => History::TYPE_UPDATE,
                    'change_amount' => $amount,
                    'balance_amount' => $amountAfter,
                    'comment_content' => $comment,
                    'is_notified' => $isNotified,
                    'currency_code' => $currencyCode,
                    'credit_currency_code' => $currencyCode,
                    'customer_name' => $customerName,
                    'customer_email' => $customer->getEmail()
                ];
                $historyModel->updateHistory($data, $storeId);

            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
