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
namespace Bss\CompanyCredit\Observer;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Helper\Email;
use Bss\CompanyCredit\Model\History;
use Bss\CompanyCredit\Model\HistoryFactory;
use Bss\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollection;
use Bss\CompanyCredit\Model\ResourceModel\HistoryRepository as HistoryRepository;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class Order Bss.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Order implements ObserverInterface
{
    /**
     * @var HistoryRepository
     */
    protected $historyRepository;

    /**
     * @var HistoryCollection
     */
    protected $historyCollection;

    /**
     * @var HistoryFactory
     */
    protected $historyCredit;

    /**
     * @var CreditRepositoryInterface
     */
    private $companyCreditRepository;

    /**
     * @var Email
     */
    protected $helperEmail;

    /**
     * @var \Bss\CompanyCredit\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\CompanyCredit\Helper\Currency
     */
    protected $helperCurrency;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var \Bss\CompanyCredit\Model\UpdatePaymentStatus
     */
    protected $paymentStatus;

    /**
     * Order constructor.
     *
     * @param HistoryRepository $historyRepository
     * @param HistoryCollection $historyCollection
     * @param HistoryFactory $historyCredit
     * @param CreditRepositoryInterface $companyCreditRepository
     * @param Email $helperEmail
     * @param \Bss\CompanyCredit\Helper\Data $helperData
     * @param \Bss\CompanyCredit\Helper\Currency $helperCurrency
     * @param DateTimeFactory $dateFactory
     */
    public function __construct(
        HistoryRepository $historyRepository,
        HistoryCollection $historyCollection,
        HistoryFactory $historyCredit,
        CreditRepositoryInterface $companyCreditRepository,
        \Bss\CompanyCredit\Helper\Email $helperEmail,
        \Bss\CompanyCredit\Helper\Data $helperData,
        \Bss\CompanyCredit\Helper\Currency $helperCurrency,
        DateTimeFactory $dateFactory,
        \Bss\CompanyCredit\Model\UpdatePaymentStatus $paymentStatus
    ) {
        $this->historyRepository = $historyRepository;
        $this->historyCollection = $historyCollection;
        $this->historyCredit = $historyCredit;
        $this->companyCreditRepository = $companyCreditRepository;
        $this->helperEmail = $helperEmail;
        $this->helperData = $helperData;
        $this->helperCurrency = $helperCurrency;
        $this->dateFactory = $dateFactory;
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * Set Company Credit
     *
     * @param Observer $observer
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->helperData->isEnableModule()) {
                $order = $observer->getEvent()->getOrder();
                if ($order && $order->getPayment()) {
                    $payment = $order->getPayment();
                    if ($payment->getMethod() == "purchaseorder") {
                        $customerId = $order->getCustomerId();
                        $orderId = $order->getId();
                        $pONumber = $payment->getPoNumber();
                        $companyCredit = $this->companyCreditRepository->get($customerId);
                        $historyCollection = $this->historyCollection->create()
                            ->addFieldToFilter("order_id", $orderId);
                        if (!$historyCollection->getSize() && $companyCredit && $companyCredit->getId()) {
                            $currencyCodeCredit = $companyCredit->getCurrencyCode();
                            $currencyCodeQuote = $order->getBaseCurrencyCode();
                            $baseOrderTotal = $this->helperCurrency
                                ->convertCurrency($order->getBaseGrandTotal(), $currencyCodeQuote, $currencyCodeCredit);
                            $availableCreditNew = $companyCredit->getAvailableCredit() - $baseOrderTotal;

                            if ($companyCredit->getPaymentDueDate()) {
                                $dateFactory = $this->dateFactory->create();
                                $timeNow = $dateFactory->gmtDate();
                                $paymentDueDate =
                                    strtotime('+' . $companyCredit->getPaymentDueDate() . ' day', strtotime($timeNow));
                                $paymentDueDate = $dateFactory->date('Y-m-d H:i:s', $paymentDueDate);
                            } else {
                                $paymentDueDate = null;
                            }

                            if ($availableCreditNew >= 0 || $companyCredit->getAllowExceed()) {
                                $usedCredit = $companyCredit->getCreditLimit() - $availableCreditNew;
                                $companyCredit->setAvailableCredit($availableCreditNew);
                                $companyCredit->setUsedCredit($usedCredit);
                                $this->companyCreditRepository->save($companyCredit);
                                $availableCreditChange = 0 - $baseOrderTotal;
                                $dataHistory = [
                                    'customer_id' => $customerId,
                                    'type' => History::TYPE_PLACE_ORDER,
                                    'change_credit' => $availableCreditChange,
                                    'available_credit_current' => $availableCreditNew,
                                    'comment' => "",
                                    'allow_exceed' => $companyCredit->getAllowExceed(),
                                    "po_number" => $pONumber,
                                    "order_id" => $orderId,
                                    'currency_code' => $currencyCodeCredit,
                                    'payment_due_date' => $paymentDueDate
                                ];
                                $history = $this->historyCredit->create();
                                $history->updateHistory($dataHistory);
                                $this->historyRepository->save($history);

                                $history = $this->historyCredit->create()->load($orderId, 'order_id');
                                if ($history->getId()) {
                                    $dataRemind['payment_status'] = $history->getPaymentStatus();
                                    $dataRemind['payment_due_date'] = $history->getPaymentDueDate();
                                    $this->paymentStatus->saveRemind($history->getId(), $dataRemind);
                                }

                                if ($availableCreditNew < 0) {
                                    $variables = [
                                        'customer_name' => $order->getCustomerName(),
                                        'order_id' => $order->getIncrementId(),
                                        'po_number' => $pONumber,
                                    ];
                                    $this->helperEmail->sendCreditLimitExceed($variables);
                                }
                            } else {
                                throw new \Magento\Framework\Exception\CouldNotSaveException(
                                    __("Sorry, you cannot place order at the moment.")
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->helperData->logError($exception->getMessage());
        }
    }
}
