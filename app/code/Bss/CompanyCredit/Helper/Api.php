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
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Helper;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Model\CreditFactory;
use Bss\CompanyCredit\Model\History;
use Bss\CompanyCredit\Model\HistoryFactory;
use Bss\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollection;
use Bss\CompanyCredit\Model\ResourceModel\HistoryRepository as HistoryRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
class Api
{
    /**
     * @var Currency
     */
    protected $helperCurrency;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CreditFactory
     */
    private $creditFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
    protected $companyCreditRepository;

    /**
     * Order constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CreditFactory $creditFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param HistoryRepository $historyRepository
     * @param HistoryFactory $historyCredit
     * @param CreditRepositoryInterface $companyCreditRepository
     * @param Currency $helperCurrency
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CreditFactory $creditFactory,
        \Psr\Log\LoggerInterface $logger,
        HistoryRepository $historyRepository,
        HistoryFactory $historyCredit,
        CreditRepositoryInterface $companyCreditRepository,
        Currency $helperCurrency
    ) {
        $this->customerRepository = $customerRepository;
        $this->creditFactory = $creditFactory;
        $this->logger = $logger;
        $this->historyRepository = $historyRepository;
        $this->historyCredit = $historyCredit;
        $this->companyCreditRepository = $companyCreditRepository;
        $this->helperCurrency = $helperCurrency;
    }

    /**
     * Save company credit
     *
     * @param string[] $saveCompanyCredit
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(
        $saveCompanyCredit
    ) {
        $this->initValue($saveCompanyCredit);
        $customerId = $saveCompanyCredit["customer_id"];

        $result = [];
        $checkFirst = 0;
        $availableCreditOld = 0;
        $creditLimitOld = 0;
        $creditLimitParam = $saveCompanyCredit['credit_limit'];
        $availableCreditParam = $saveCompanyCredit['update_available'];
        $comment = $comment = $saveCompanyCredit['comment'];
        $allowExceed = $saveCompanyCredit['allow_exceed'];
        $allowExceedOld = $allowExceed;

        try {
            $credit = $this->companyCreditRepository->get($customerId);
            $historyModel = $this->historyCredit->create();
            if ($credit && $credit->getId()) {
                $this->convertCurrency($saveCompanyCredit);
                $allowExceedOld = $credit->getAllowExceed();
                $availableCreditOld = $credit->getAvailableCredit();
                $creditLimitOld = $credit->getCreditLimit();
                if (!$creditLimitParam && !(is_numeric($creditLimitParam))) {
                    $creditLimitParam = $creditLimitOld;
                }
                if (is_numeric($saveCompanyCredit["allow_exceed"]) === false) {
                    $allowExceed = $credit->getAllowExceed();
                }
                $availableCreditChange = $creditLimitParam - $creditLimitOld + (float)($availableCreditParam);
            } else {
                $this->firstSave($saveCompanyCredit);
                $this->convertCurrency($saveCompanyCredit);
                $availableCreditChange = $creditLimitParam - $creditLimitOld;
                $checkFirst = 1;
                $credit = $this->creditFactory->create();
            }
            $availableCredit = $availableCreditChange + $availableCreditOld;
            $usedCredit = $creditLimitParam - $availableCredit;
            if (
                $usedCredit >= 0 &&
                ($allowExceed || ($availableCredit >= 0 && $availableCredit <= $creditLimitParam))
            ) {
                if ($checkFirst || $saveCompanyCredit['update_available'] ||
                    $saveCompanyCredit['credit_limit'] != $creditLimitOld
                    || $allowExceedOld != $allowExceed) {
                    $dataHistory = [
                        'customer_id' => $customerId,
                        'type' => \Bss\CompanyCredit\Model\History::TYPE_ADMIN_REFUND,
                        'change_credit' => $availableCreditChange,
                        'available_credit_current' => $availableCredit,
                        'comment' => $comment,
                        'allow_exceed' => $allowExceed,
                        'currency_code' => $saveCompanyCredit["currency_code_website"]
                    ];
                    $credit->setAvailableCredit($availableCredit);
                    $credit->setUsedCredit($usedCredit);
                    $credit->setCreditLimit($creditLimitParam);
                    $credit->setAllowExceed($allowExceed);
                    $credit->setCustomerId($customerId);
                    $credit->setCurrencyCode($saveCompanyCredit["currency_code_website"]);
                    $this->companyCreditRepository->save($credit);
                    $checkSave = 0;
                    if ($saveCompanyCredit['update_available']) {
                        $saveCompanyCredit["available_credit_current"] =
                            $availableCreditOld + $saveCompanyCredit['update_available'];
                        $this->updateCreditValue($saveCompanyCredit, $dataHistory, $historyModel);
                        $checkSave = 1;
                    }
                    if ($checkFirst || ($creditLimitParam != $creditLimitOld)) {
                        $this->changeCreditLimit($saveCompanyCredit, $dataHistory, $creditLimitOld, $historyModel);
                        $checkSave = 1;
                    }
                    if ($allowExceedOld != $allowExceed || $checkFirst) {
                        $this->allowExceedCredit($dataHistory, $historyModel);
                        $checkSave = 1;
                    }
                    if ($checkSave) {
                        $result["status"] = [
                            "success" => true,
                            "message" => __("You have successfully saved changes to company credit.")->render()
                        ];
                    }
                }
            } else {
                $result["status"] = [
                    "success" => false,
                    "message" => __("You cannot update available credit to greater than credit limit.")->render()
                ];
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result["status"] = [
                "success" => false,
                "message" => __("Some time error.")->render()
            ];
        }

        return $result;
    }

    /**
     * Save company credit
     *
     * @param string[] $saveCompanyCredit
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(ExcessiveMethodLength)
     */
    public function saveDirectAvaliableCredit(
        $saveCompanyCredit
    ) {
        $this->initValue($saveCompanyCredit);
        $customerId = $saveCompanyCredit["customer_id"];

        $result = [];
        $checkFirst = 0;
        $availableCreditOld = 0;
        $creditLimitOld = 0;
        $creditLimitParam = $saveCompanyCredit['credit_limit'];
        $availableCreditParam = $saveCompanyCredit['available_credit'];
        $comment = $comment = $saveCompanyCredit['comment'];
        $allowExceed = $saveCompanyCredit['allow_exceed'];
        $allowExceedOld = $allowExceed;

        try {
            $credit = $this->companyCreditRepository->get($customerId);
            $historyModel = $this->historyCredit->create();
            $saveCompanyCredit["allow_exceed"] = $allowExceed;
            if ($credit && $credit->getId()) {
                if (!isset($saveCompanyCredit["available_credit"])) {
                    return  $result["status"] = [
                        "success" => false,
                        "message" => __("Input available_credit is required. When you update credit_limit or available credit")->render()
                    ];
                }
                if ($this->checkNotChange($credit, $saveCompanyCredit)) {
                    return [];
                }
                $this->convertCurrency($saveCompanyCredit);
                $allowExceedOld = $credit->getAllowExceed();
                $availableCreditOld = $credit->getAvailableCredit();
                $creditLimitOld = $credit->getCreditLimit();
                if (!$creditLimitParam && !(is_numeric($creditLimitParam))) {
                    $creditLimitParam = $creditLimitOld;
                }
                if (is_numeric($saveCompanyCredit["allow_exceed"]) === false) {
                    $allowExceed = $credit->getAllowExceed();
                }
                if (is_numeric($saveCompanyCredit["available_credit"]) === false) {
                    $availableCreditParam = $availableCreditOld;
                }
                $availableCreditChange = $creditLimitParam - $creditLimitOld + (float)($availableCreditParam) - $availableCreditOld;
            } else {
                $this->firstSave($saveCompanyCredit);
                $this->convertCurrency($saveCompanyCredit);
                $availableCreditParam = $creditLimitParam;
                $availableCreditChange = $creditLimitParam;
                $checkFirst = 1;
                $credit = $this->creditFactory->create();
            }
            $availableCredit = $availableCreditParam;
            $usedCredit = $creditLimitParam - $availableCredit;
            if ($usedCredit >= 0 && ($allowExceed || ($availableCredit >= 0 && $availableCredit <= $creditLimitParam))) {
                if ($checkFirst || $saveCompanyCredit['available_credit'] ||
                    $saveCompanyCredit['credit_limit'] != $creditLimitOld
                    || $allowExceedOld != $allowExceed) {
                    $dataHistory = [
                        'customer_id' => $customerId,
                        'type' => \Bss\CompanyCredit\Model\History::TYPE_ADMIN_REFUND,
                        'change_credit' => $availableCreditChange,
                        'available_credit_current' => $availableCredit,
                        'comment' => $comment,
                        'allow_exceed' => $allowExceed,
                        'currency_code' => $saveCompanyCredit["currency_code_website"]
                    ];
                    $credit->setAvailableCredit($availableCredit);
                    $credit->setUsedCredit($usedCredit);
                    $credit->setCreditLimit($creditLimitParam);
                    $credit->setAllowExceed($allowExceed);
                    $credit->setCustomerId($customerId);
                    $credit->setCurrencyCode($saveCompanyCredit["currency_code_website"]);
                    $this->companyCreditRepository->save($credit);
                    $checkSave = 0;
                    if ($saveCompanyCredit['available_credit']) {
                        $saveCompanyCredit["update_available"] = $availableCreditParam - $availableCreditOld;
                        $saveCompanyCredit["available_credit_current"] = $saveCompanyCredit['available_credit'];
                        if ($saveCompanyCredit["update_available"]) {
                            $this->updateCreditValue($saveCompanyCredit, $dataHistory, $historyModel);
                        }
                        $checkSave = 1;
                    }
                    if ($checkFirst || ($creditLimitParam != $creditLimitOld)) {
                        $this->changeCreditLimit($saveCompanyCredit, $dataHistory, $creditLimitOld, $historyModel);
                        $checkSave = 1;
                    }
                    if ($allowExceedOld != $allowExceed || $checkFirst) {
                        $this->allowExceedCredit($dataHistory, $historyModel);
                        $checkSave = 1;
                    }
                    if ($checkSave) {
                        $result["status"] = [
                            "success" => true,
                            "message" => __("You have successfully saved changes to company credit.")->render()
                        ];
                    }
                }
            } else {
                $result["status"] = [
                    "success" => false,
                    "message" => __("You cannot update available credit to greater than credit limit.")->render()
                ];
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result["status"] = [
                "success" => false,
                "message" => __("%1", $e->getMessage())->render()
            ];
        }

        return $result;
    }

    /**
     * Init value input
     *
     * @param array $saveCompanyCredit
     * @param string $baseCurrency
     */
    public function initValue(&$saveCompanyCredit)
    {
        if (!isset($saveCompanyCredit['credit_limit'])) {
            $saveCompanyCredit['credit_limit'] = "";
        }

        if (!isset($saveCompanyCredit["update_available"])) {
            $saveCompanyCredit["update_available"] = "";
        }

        if (!isset($saveCompanyCredit["available_credit"])) {
            $saveCompanyCredit["available_credit"] = "";
        }

        if (!isset($saveCompanyCredit['comment'])) {
            $saveCompanyCredit['comment'] = "";
        }

        if (!isset($saveCompanyCredit['allow_exceed'])) {
            $saveCompanyCredit['allow_exceed'] = "";
        }

        if (!isset($saveCompanyCredit['order_id'])) {
            $saveCompanyCredit['order_id'] = null;
        }

        if (!isset($saveCompanyCredit['po_number'])) {
            $saveCompanyCredit['po_number'] = null;
        }

        $this->getCurrencyCodeWebsite($saveCompanyCredit);
    }

    /**
     * Get currency code by website
     *
     * @param array $saveCompanyCredit
     * @param string $baseCurrencyCode
     */
    public function getCurrencyCodeWebsite(&$saveCompanyCredit)
    {
        try {
            $customer = $this->customerRepository->getById($saveCompanyCredit["customer_id"]);
            $saveCompanyCredit["currency_code_website"] = $this->helperCurrency->getCurrencyCodeByWebsite($customer->getWebsiteId());
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            $saveCompanyCredit["currency_code_website"] = $saveCompanyCredit["baseCurrencyCode"];
        }
        if (!isset($saveCompanyCredit["currency_code"])) {
            $saveCompanyCredit["currency_code"] = $saveCompanyCredit["currency_code_website"];
        }
    }

    /**
     * Ignore value input update_available, order_id, po_number
     * When not assign Company Credit
     *
     * @param array $saveCompanyCredit
     * @throws InputException
     */
    public function firstSave(&$saveCompanyCredit)
    {
        if (!$saveCompanyCredit["credit_limit"]) {
            throw new InputException(__('Input credit_limit is required. When a new company credit account is created .'));
        }
        $saveCompanyCredit["update_available"] = "";
        $saveCompanyCredit["available_credit"] = "";
        $saveCompanyCredit['order_id'] = "";
        $saveCompanyCredit["po_number"] = "";
    }

    /**
     * Convert currency input update_available and credit_limit
     *
     * @param array $saveCompanyCredit
     */
    public function convertCurrency(&$saveCompanyCredit)
    {
        if ($saveCompanyCredit["credit_limit"]) {
            $saveCompanyCredit["credit_limit"] =
                $this->helperCurrency->convertCurrency($saveCompanyCredit["credit_limit"], $saveCompanyCredit["currency_code"], $saveCompanyCredit["currency_code_website"]);
        }
        if ($saveCompanyCredit["update_available"]) {
            $saveCompanyCredit["update_available"] =
                $this->helperCurrency->convertCurrency($saveCompanyCredit["update_available"], $saveCompanyCredit["currency_code"], $saveCompanyCredit["currency_code_website"]);
        }
        if ($saveCompanyCredit["available_credit"]) {
            $saveCompanyCredit["available_credit"] =
                $this->helperCurrency->convertCurrency($saveCompanyCredit["available_credit"], $saveCompanyCredit["currency_code"], $saveCompanyCredit["currency_code_website"]);
        }
    }

    /**
     * Save credit limit and send email for customer
     *
     * @param array $params
     * @param array $dataHistory
     * @param float $creditLimitOld
     * @param History $historyModel
     * @throws LocalizedException
     */
    public function changeCreditLimit($params, $dataHistory, $creditLimitOld, $historyModel)
    {
        $dataHistory["type"] = History::TYPE_ADMIN_CHANGES_CREDIT_LIMIT;
        $dataHistory["change_credit"] = $params['credit_limit'] - $creditLimitOld;
        $historyModel->updateHistory($dataHistory);
        $this->historyRepository->save($historyModel);
    }

    /**
     * Save update credit value and send email for customer
     *
     * @param array $params
     * @param array $dataHistory
     * @param History $historyModel
     * @throws LocalizedException
     */
    public function updateCreditValue($params, $dataHistory, $historyModel)
    {
        if ($params["order_id"]) {
            $dataHistory["order_id"] = $params["order_id"];
            $dataHistory["po_number"] = $params["po_number"];
            $dataHistory["type"] = \Bss\CompanyCredit\Model\History::TYPE_PLACE_ORDER;
        }
        $dataHistory["change_credit"] = $params['update_available'];
        $dataHistory["available_credit_current"] = $params["available_credit_current"];
        $historyModel->updateHistory($dataHistory);
        $this->historyRepository->save($historyModel);
    }

    /**
     * Save change allow exceed of customer
     *
     * @param array $dataHistory
     * @param History $historyModel
     * @throws LocalizedException
     */
    public function allowExceedCredit($dataHistory, $historyModel)
    {
        $dataHistory["change_credit"] = 0;
        $dataHistory["type"] = History::TYPE_CHANGE_CREDIT_EXCESS_TO;
        $historyModel->updateHistory($dataHistory);
        $this->historyRepository->save($historyModel);
    }

    /**
     * Check not change company credit
     *
     * @param \Bss\CompanyCredit\Model\Credit $credit
     * @param array $saveCompanyCredit
     * @return bool
     */
    public function checkNotChange($credit, $saveCompanyCredit)
    {
        if ($saveCompanyCredit["available_credit"] == $credit->getAvailableCredit() &&
            (is_numeric($saveCompanyCredit["credit_limit"]) === false || $saveCompanyCredit["credit_limit"] == $credit->getCreditLimit()) &&
            (is_numeric($saveCompanyCredit["allow_exceed"]) === false || $saveCompanyCredit["allow_exceed"] == $credit->getAllowExceed())
        ) {
            return true;
        }
        return false;
    }
}
