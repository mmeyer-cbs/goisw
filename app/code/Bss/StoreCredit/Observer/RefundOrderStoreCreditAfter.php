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
namespace Bss\StoreCredit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Bss\StoreCredit\Helper\Data;
use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Psr\Log\LoggerInterface;
use Bss\StoreCredit\Model\HistoryFactory;
use Magento\Framework\Event\Observer;
use Bss\StoreCredit\Model\History;

/**
 * Class RefundOrderStoreCreditAfter
 */
class RefundOrderStoreCreditAfter implements ObserverInterface
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
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

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
     * @param RequestInterface $request
     * @param Data $bssStoreCreditHelper
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param LoggerInterface $logger
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        RequestInterface $request,
        Data $bssStoreCreditHelper,
        StoreCreditRepositoryInterface $storeCreditRepository,
        LoggerInterface $logger,
        HistoryFactory $historyFactory
    ) {
        $this->currency = $currency;
        $this->request = $request;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->storeCreditRepository = $storeCreditRepository;
        $this->logger = $logger;
        $this->historyFactory = $historyFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $params = $this->request->getParams();
        $creditmemo = $observer->getEvent()->getCreditmemo();
        try {
            $customerId = $creditmemo->getCustomerId();
            if ($customerId == null) {
                $history = $this->historyFactory->create()->loadByOrder($params['order_id']);
                $customerId = $history->getCustomerId();
            }
            if ($customerId) {
                $websiteId = $creditmemo->getStore()->getWebsiteId();
                $credit = $this->storeCreditRepository->get($customerId, $websiteId);
                if (!$credit->getId()) {
                    return;
                }
                $baseGrandTotal = $creditmemo->getBaseGrandTotal();
                $historyModel = $this->historyFactory->create();
                if (isset($params['creditmemo']['storecredit']) && $params['creditmemo']['storecredit']) {
                    $baseStorecreditRefund = $creditmemo->getBaseBssStorecreditAmount() + $baseGrandTotal;
                } else {
                    $baseStorecreditRefund = $creditmemo->getBaseBssStorecreditAmount();
                }
                $creditCurrencyCode = $this->currency->getCreditCurrencyCode($credit->getCurrencyCode(), $websiteId) ;
                $baseAmountUpdate = $credit->getBalanceAmount();

                $dataCustomer = [
                    "customerName" =>  $creditmemo->getOrder()->getCustomerName(),
                    "customerEmail" => $creditmemo->getOrder()->getCustomerEmail()
                ];

                if ($baseStorecreditRefund) {
                    $data = [
                        'customer_id' => $customerId,
                        'creditmemo_id' => $creditmemo->getId(),
                        'order_id' => $creditmemo->getOrderId(),
                        'website_id' => $websiteId,
                        'type' => History::TYPE_REFUND,
                        'change_amount' => $this->currency->convertCurrency($baseStorecreditRefund, $creditmemo->getBaseCurrencyCode(), $creditCurrencyCode),
                        'balance_amount' => $baseAmountUpdate,
                        'comment_content' => null,
                        'is_notified' => true,
                        'customer_name' => $creditmemo->getOrder()->getCustomerName(),
                        'customer_email' => $creditmemo->getOrder()->getCustomerEmail(),
                        'currency_code' => $creditmemo->getOrderCurrencyCode(),
                        'credit_currency_code' => $creditCurrencyCode,
                        'change_amount_store_view' =>  $this->currency->convertCurrency($baseStorecreditRefund, $creditmemo->getBaseCurrencyCode(), $creditmemo->getOrderCurrencyCode())
                    ];
                    $historyModel->updateHistory($data, $creditmemo->getStoreId(), $dataCustomer);

                }
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
