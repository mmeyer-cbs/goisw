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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Model;

use Bss\CompanyCredit\Api\Data\HistoryInterface;
use Bss\CompanyCredit\Api\HistoryRepositoryInterface as HistoryRepository;
use Bss\CompanyCredit\Api\RemindRepositoryInterface as RemindRepository;
use Bss\CompanyCredit\Helper\Email;
use Bss\CompanyCredit\Model\ResourceModel\Credit\CollectionFactory as CreditCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class UpdatePaymentStatus
{
    const TOTAL_PAID = 'Total Paid';
    const PARTIAL_PAID = 'Partial Paid';

    /**
     * @var CreditCollection
     */
    protected $creditCollection;

    /**
     * @var HistoryRepository
     */
    protected $historyRepository;

    /**
     * @var RemindRepository
     */
    protected $remindRepository;

    /**
     * @var RemindFactory
     */
    protected $remindFactory;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var int|string|null
     */
    protected $daySendMailBeforeOverdue;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $createDateFactory;

    /**
     * Construct.
     *
     * @param CreditCollection $creditCollection
     * @param HistoryRepository $historyRepository
     * @param RemindRepository $remindRepository
     * @param RemindFactory $remindFactory
     * @param Email $emailHelper
     * @param DateTimeFactory $dateFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CreditCollection $creditCollection,
        HistoryRepository $historyRepository,
        RemindRepository $remindRepository,
        RemindFactory $remindFactory,
        Email $emailHelper,
        DateTimeFactory $dateFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->creditCollection = $creditCollection;
        $this->historyRepository = $historyRepository;
        $this->remindRepository = $remindRepository;
        $this->remindFactory = $remindFactory;
        $this->emailHelper = $emailHelper;
        $this->dateFactory = $dateFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Upgrade table customer_form_attribute.
     *
     * @param \Bss\CompanyCredit\Api\CreditRepositoryInterface|mixed $credit
     * @param int|null $availableCreditParam
     * @param bool $updateCreditDue
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute($credit, $availableCreditParam, $updateCreditDue = false)
    {
        $data = [];
        if ($availableCreditParam) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter("customer_id", $credit->getCustomerId())
                ->addFilter("type", 1);
            $companyHistoryCreditItems = $this->historyRepository->getList($searchCriteria->create())->getItems();

            if ($availableCreditParam > 0) {
                foreach ($companyHistoryCreditItems as $itemHistory) {
                    if ($itemHistory["payment_status"] != self::TOTAL_PAID) {
                        $itemHistory["change_credit"] = $itemHistory["change_credit"] ?? 0;

                        $priceUnpaid = $itemHistory["unpaid_credit"]
                            ? $itemHistory["unpaid_credit"] : abs($itemHistory["change_credit"]);
                        $availableCreditParam -= $priceUnpaid;
                        $data[$itemHistory['id']]['payment_due_date'] =
                            $this->getPaymentDueDate($credit->getPaymentDueDate(), $itemHistory);
                        if ($availableCreditParam >= 0) {
                            $data[$itemHistory['id']]['payment_status'] = self::TOTAL_PAID;
                            $data[$itemHistory['id']]['unpaid_credit'] = null;

                            $this->saveHistory($itemHistory, $data[$itemHistory['id']]);
                            $this->saveRemind($itemHistory['id'], $data[$itemHistory['id']]);
                        } else {
                            $data[$itemHistory['id']]['payment_status'] = self::PARTIAL_PAID;
                            $data[$itemHistory['id']]['unpaid_credit'] = abs($availableCreditParam);

                            $this->saveHistory($itemHistory, $data[$itemHistory['id']]);
                            $this->saveRemind($itemHistory['id'], $data[$itemHistory['id']]);
                            break;
                        }
                    }
                }
            } else {
                $companyHistoryCreditItems = array_reverse($companyHistoryCreditItems);
                foreach ($companyHistoryCreditItems as $itemHistory) {
                    if ($itemHistory["payment_status"] == null) {
                        continue;
                    }

                    $itemHistory["change_credit"] = $itemHistory["change_credit"] ?? 0;

                    $priceUnpaid = $itemHistory["unpaid_credit"] ? $itemHistory["unpaid_credit"] : 0;
                    $total = $priceUnpaid + abs($availableCreditParam);
                    if ($total >= abs($itemHistory["change_credit"])) {
                        $availableCreditParam = $total - abs($itemHistory["change_credit"]);
                        $data[$itemHistory['id']]['payment_status'] = null;
                        $data[$itemHistory['id']]['unpaid_credit'] = null;
                        $data[$itemHistory['id']]['payment_due_date'] =
                            $this->getPaymentDueDate($credit->getPaymentDueDate(), $itemHistory, $data[$itemHistory['id']]['payment_status']);

                        $this->saveHistory($itemHistory, $data[$itemHistory['id']]);
                        $this->saveRemind($itemHistory['id'], $data[$itemHistory['id']]);
                        if ($total == abs($itemHistory["change_credit"])) {
                            break;
                        }
                    } else {
                        $data[$itemHistory['id']]['payment_status'] = self::PARTIAL_PAID;
                        $data[$itemHistory['id']]['unpaid_credit'] = $total;
                        $data[$itemHistory['id']]['payment_due_date'] =
                            $this->getPaymentDueDate($credit->getPaymentDueDate(), $itemHistory, $data[$itemHistory['id']]['payment_status']);

                        $this->saveHistory($itemHistory, $data[$itemHistory['id']]);
                        $this->saveRemind($itemHistory['id'], $data[$itemHistory['id']]);
                        break;
                    }
                }
            }
        }

        if ($updateCreditDue) {
            $this->updatePaymentDueDate($credit);
        }
    }

    /**
     * Update single credit.
     *
     * @param \Bss\CompanyCredit\Api\CreditRepositoryInterface|mixed $credit
     * @param int $dayDueDate
     * @return void
     */
    public function updatePaymentDueDate($credit, $dayDueDate = null)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter("customer_id", $credit->getCustomerId())
            ->addFilter("type", 1);
        $companyHistoryCreditItems = $this->historyRepository->getList($searchCriteria->create())->getItems();

        $dayDueDate = $dayDueDate !== null ? $dayDueDate : $credit->getPaymentDueDate();
        foreach ($companyHistoryCreditItems as $itemHistory) {
            if ($itemHistory["payment_status"] != self::TOTAL_PAID) {
                $data[$itemHistory['id']]['payment_due_date'] =
                    $this->getPaymentDueDate($dayDueDate, $itemHistory);
                $data[$itemHistory['id']]['payment_status'] = $itemHistory['payment_status'];
                $data[$itemHistory['id']]['unpaid_credit'] = $itemHistory['unpaid_credit'];

                $this->saveHistory($itemHistory, $data[$itemHistory['id']]);
                $this->saveRemind($itemHistory['id'], $data[$itemHistory['id']]);
            }
        }
    }

    /**
     * Update single credit.
     *
     * @param array $dataHistory
     * @param int|null $dayDueDate
     * @param \Bss\CompanyCredit\Api\CreditRepositoryInterface|mixed $credit
     * @return array
     */
    public function updateSingleCredit($dataHistory, $dayDueDate, $credit)
    {
        $data = [];
        $result = [];

        $result['total'] = 0;
        $result['comment'] = "Update PO: ";
        foreach ($dataHistory as $historyId => $updatePaid) {
            if ($updatePaid && is_numeric((float) $updatePaid)) {
                $itemHistory = $this->historyRepository->getById($historyId);
                $dataItemHistory[$itemHistory->getId()] = $itemHistory;
                $unpaidCredit = $itemHistory->getUnpaidCredit() ?
                    $itemHistory->getUnpaidCredit() : abs((float) $itemHistory->getChangeCredit());
                $unpaidCredit -= $updatePaid;
                if ($unpaidCredit >= 0 && $unpaidCredit <= abs((float) $itemHistory->getChangeCredit())) {
                    if ($unpaidCredit == 0) {
                        $data[$historyId]['payment_status'] = self::TOTAL_PAID;
                        $data[$historyId]['unpaid_credit'] = null;
                    } elseif ($unpaidCredit == abs((float) $itemHistory->getChangeCredit())) {
                        $data[$historyId]['payment_status'] = null;
                        $data[$historyId]['unpaid_credit'] = null;
                    } else {
                        $data[$historyId]['payment_status'] = self::PARTIAL_PAID;
                        $data[$historyId]['unpaid_credit'] = $unpaidCredit;
                    }

                    $data[$historyId]['payment_due_date'] =
                        $itemHistory->getPaymentDueDate();
                    $data[$historyId]['id'] =
                        $itemHistory->getId();

                    $result['total'] += $updatePaid;
                    $result['comment'] .= $itemHistory->getPoNumber() . ', ';
                } else {
                    $result = [];
                    $data = [];
                    break;
                }
            }
        }

        if ($data && isset($historyId)) {
            foreach ($data as $item) {
                if (isset($dataItemHistory[$item['id']])) {
                    $this->saveHistory($dataItemHistory[$item['id']], $item);
                    $this->saveRemind($historyId, $item);
                }
            }
        }

        if ($dayDueDate) {
            $this->updatePaymentDueDate($credit, $dayDueDate);
        }
        return $result;
    }

    /**
     * Save credit history.
     *
     * @param HistoryInterface|mixed $historyModel
     * @param array $historyItem
     * @return void
     */
    public function saveHistory($historyModel, $historyItem)
    {
        $checkSave = 0;

        if ($historyModel->getPaymentDueDate() != $historyItem['payment_due_date']) {
            $historyModel->setPaymentDueDate($historyItem['payment_due_date']);
            $checkSave++;
        }

        if ($historyModel->getPaymentStatus() != $historyItem['payment_status']) {
            $historyModel->setPaymentStatus($historyItem['payment_status']);
            $checkSave++;
        }

        if ($historyModel->getUnpaidCredit() != $historyItem['unpaid_credit']) {
            $historyModel->setUnpaidCredit($historyItem['unpaid_credit']);
            $checkSave++;
        }

        if ($checkSave) {
            $this->historyRepository->save($historyModel);
        }
    }

    /**
     * Save remind table
     *
     * @param int $historyId
     * @param array $historyItem
     * @return void
     */
    public function saveRemind($historyId, $historyItem)
    {
        $remindModel = $this->remindRepository->getByIdHistory($historyId);
        if ($historyItem['payment_status'] != self::TOTAL_PAID) {
            if (!$this->daySendMailBeforeOverdue) {
                $this->daySendMailBeforeOverdue = $this->emailHelper->getDaySendMailBeforeOverdue();
            }

            $daySendMail = $this->getDaySendMail($historyItem['payment_due_date'], $this->daySendMailBeforeOverdue);

            if (!$remindModel || !$remindModel->getId()) {
                $remindModel = $this->remindFactory->create();
                $remindModel->setIdHistory($historyId);
                $remindModel->setSendingTime($daySendMail);
                $this->remindRepository->save($remindModel);
            } else {
                if ($remindModel->getSendingTime() != $daySendMail) {
                    $remindModel->setSendingTime($daySendMail);
                    $this->remindRepository->save($remindModel);
                }
            }
        } else {
            $this->remindRepository->delete($remindModel);
        }
    }

    /**
     * Upgrade multiple data.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function executeFirstUpgradeData()
    {
        $collection = $this->creditCollection->create();
        $companyCreditItems = $collection->getItems();

        if ($companyCreditItems) {
            $data = [];
            foreach ($companyCreditItems as $itemCredit) {
                $creditUsed = 0;
                $noPaid = true;
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter("customer_id", $itemCredit["customer_id"])
                    ->addFilter("type", 1);
                $companyHistoryCreditItems = $this->historyRepository->getList($searchCriteria->create())->getItems();
                $companyHistoryCreditItems = array_reverse($companyHistoryCreditItems);
                foreach ($companyHistoryCreditItems as $itemHistory) {
                    if ($itemHistory['customer_id'] == $itemCredit["customer_id"]) {
                        if ($itemCredit["used_credit"] > 0) {
                            if ($noPaid) {
                                $itemHistory['change_credit'] = $itemHistory['change_credit'] ?? 0;

                                $creditUsed += abs($itemHistory['change_credit']);
                                $unpaidCredit = $itemCredit["used_credit"] - $creditUsed;
                                if ($unpaidCredit >= 0) {
                                    $data[$itemHistory['id']]['payment_status'] = null;
                                    $data[$itemHistory['id']]['unpaid_credit'] = null;
                                    if ($unpaidCredit == 0) {
                                        $noPaid = false;
                                    }
                                } else {
                                    $unpaidCredit = abs($itemHistory['change_credit']) - abs($unpaidCredit);
                                    $data[$itemHistory['id']]['payment_status'] = self::PARTIAL_PAID;
                                    $data[$itemHistory['id']]['unpaid_credit'] = $unpaidCredit;
                                    $noPaid = false;
                                }
                            } else {
                                $data[$itemHistory['id']]['payment_status'] = self::TOTAL_PAID;
                                $data[$itemHistory['id']]['unpaid_credit'] = null;
                            }
                        } else {
                            $data[$itemHistory['id']]['payment_status'] = self::TOTAL_PAID;
                            $data[$itemHistory['id']]['unpaid_credit'] = null;
                        }

                        $data[$itemHistory['id']]['payment_due_date'] = null;

                        $this->saveHistory($itemHistory, $data[$itemHistory['id']]);
                    }
                }
            }

            if ($data) {
                $this->updateFirstData($data);
            }
        }
    }

    /**
     * Insert multiple remind.
     *
     * @param array $data
     * @return void
     */
    public function updateFirstData($data)
    {
        $dataMultiRemind = [];
        foreach ($data as $historyId => $historyItem) {
            if ($historyItem['payment_status'] != self::TOTAL_PAID) {
                $dataMultiRemind[$historyId]['id_history'] = $historyId;
            }
        }

        if ($dataMultiRemind) {
            $this->remindRepository->insertMultiple($dataMultiRemind);
        }
    }

    /**
     * Get sending_time remind.
     *
     * @param string|null|mixed $paymentDueDate
     * @param int|null|mixed $dayMailBeforeOverdue
     * @return false|string|null
     */
    public function getDaySendMail($paymentDueDate, $dayMailBeforeOverdue)
    {
        if ($paymentDueDate && $dayMailBeforeOverdue) {
            $date = strtotime('-' . $dayMailBeforeOverdue . ' day', strtotime($paymentDueDate));
            if (!$this->createDateFactory) {
                $this->createDateFactory = $this->dateFactory->create();
            }
            $daySendMail = $this->createDateFactory->date('Y-m-d H:i:s', $date);
        } else {
            $daySendMail = null;
        }

        return $daySendMail;
    }

    /**
     * Get Payment Due Date table history.
     *
     * @param int|null $dayDueDate
     * @param object $itemHistory
     * @param string|null $paymentStatusUpdate
     * @return false|mixed|string|null
     */
    public function getPaymentDueDate($dayDueDate, $itemHistory, $paymentStatusUpdate = self::TOTAL_PAID)
    {
        if ($dayDueDate && $dayDueDate > 0) {
            if ($itemHistory->getPaymentStatus() != self::TOTAL_PAID || $paymentStatusUpdate != self::TOTAL_PAID) {
                $date = strtotime('+' . $dayDueDate . ' day', strtotime($itemHistory->getCreatedTime()));
                if (!$this->createDateFactory) {
                    $this->createDateFactory = $this->dateFactory->create();
                }
                $paymentDueDate = $this->createDateFactory->date('Y-m-d H:i:s', $date);
            } else {
                $paymentDueDate = $itemHistory->getPaymentDueDate();
            }
        } else {
            $paymentDueDate = null;
        }

        return $paymentDueDate;
    }

    /**
     * Show payment status.
     *
     * @param string|null $paymentStatus
     * @param string|null $paymentDueDate
     * @return string|null
     */
    public function showPaymentStatus($paymentStatus, $paymentDueDate)
    {
        $value = $paymentStatus;
        if (!$this->createDateFactory) {
            $this->createDateFactory = $this->dateFactory->create();
        }
        $gmtDate = $this->createDateFactory->gmtDate();

        if (
            $paymentDueDate &&
            strtotime($gmtDate) >= strtotime($paymentDueDate)
        ) {
            if ($value != self::TOTAL_PAID) {
                $value = 'Overdue';
            }
        }

        return $value;
    }
}
