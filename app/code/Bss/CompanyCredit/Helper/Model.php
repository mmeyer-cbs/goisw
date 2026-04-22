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
namespace Bss\CompanyCredit\Helper;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Api\HistoryRepositoryInterface as HistoryRepository;
use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Bss\CompanyCredit\Helper\Email as HelperEmail;
use Bss\CompanyCredit\Model\CreditFactory;
use Bss\CompanyCredit\Model\History;
use Bss\CompanyCredit\Model\HistoryFactory;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class CompanyCreditCustomerSave.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Model extends AbstractHelper
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var HistoryRepository
     */
    protected $historyRepository;

    /**
     * @var Currency
     */
    protected $helperCurrency;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteReposiory;

    /**
     * @var HelperEmail
     */
    protected $helperEmail;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var CreditFactory
     */
    private $creditFactory;

    /**
     * @var CreditRepositoryInterface
     */
    private $companyCreditRepository;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Bss\CompanyCredit\Model\UpdatePaymentStatus
     */
    protected $updatePaymentStatus;

    /**
     * Model constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param HistoryRepository $historyRepository
     * @param Currency $helperCurrency
     * @param ManagerInterface $messageManager
     * @param WebsiteRepositoryInterface $websiteReposiory
     * @param HelperEmail $helperEmail
     * @param CreditFactory $creditFactory
     * @param Data $helperData
     * @param CreditRepositoryInterface $paramsRepository
     * @param HistoryFactory $historyFactory
     * @param \Bss\CompanyCredit\Model\UpdatePaymentStatus $updatePaymentStatus
     * @param Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        AuthorizationInterface $authorization,
        HistoryRepository $historyRepository,
        HelperCurrency $helperCurrency,
        ManagerInterface $messageManager,
        WebsiteRepositoryInterface $websiteReposiory,
        HelperEmail $helperEmail,
        CreditFactory $creditFactory,
        Data $helperData,
        CreditRepositoryInterface $paramsRepository,
        HistoryFactory $historyFactory,
        \Bss\CompanyCredit\Model\UpdatePaymentStatus $updatePaymentStatus,
        Context $context
    ) {
        $this->authorization = $authorization;
        $this->historyRepository = $historyRepository;
        $this->helperCurrency = $helperCurrency;
        $this->messageManager = $messageManager;
        $this->websiteReposiory = $websiteReposiory;
        $this->helperEmail = $helperEmail;
        $this->creditFactory = $creditFactory;
        $this->helperData = $helperData;
        $this->companyCreditRepository = $paramsRepository;
        $this->historyFactory = $historyFactory;
        $this->updatePaymentStatus = $updatePaymentStatus;
        parent::__construct($context);
    }

    /**
     * Save Company Credit
     *
     * @params string $action
     * @param CustomerInterface|Customer $customer
     * @param array $params
     * @param null|string $action
     * @param null|array $dataMessage
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function saveCompanyCredit($customer, $params, $action = null, &$dataMessage = null)
    {
        $customerId = $customer->getId();
        $customerName = $customer->getFirstname() . " " . $customer->getLastname();

        $checkFirst = 0;
        $availableCreditOld = 0;
        $creditLimitOld = 0;
        $creditLimitParam = $params['credit_limit'];
        $availableCreditParam = $params['update_available'];
        $comment = $params['comment'];
        $allowExceed = $params['allow_exceed'];
        $allowExceedOld = $allowExceed;
        $paymentDueDate = $params['payment_due_date'] ?? null;
        $paymentDueDateOld = $paymentDueDate;

        try {
            $credit = $this->companyCreditRepository->get($customerId);
            $historyModel = $this->historyFactory->create();
            if ($credit && $credit->getId()) {
                $allowExceedOld = $credit->getAllowExceed();
                $availableCreditOld = $credit->getAvailableCredit();
                $creditLimitOld = $credit->getCreditLimit();
                $paymentDueDateOld = $credit->getPaymentDueDate();

                if (isset($params['update_paid'])) {
                    if (!$params['update_available']) {
                        $dayDueDate = $paymentDueDate != $paymentDueDateOld ? $paymentDueDate : null;
                        $resultSingleCredit = $this->updatePaymentStatus->updateSingleCredit($params['update_paid'], $dayDueDate, $credit);

                        if ($resultSingleCredit) {
                            if ($resultSingleCredit['comment'] != "Update PO: ") {
                                $totalChangeCredit = $availableCreditParam = $resultSingleCredit['total'];
                                $commentChangeCredit = $resultSingleCredit['comment'];
                                $changeSingleCredit = true;
                            }
                        } else {
                            $this->messageManager->addErrorMessage(__("You need to enter a value to ensure that Unpaid Credit does not exceed the order value or be less than 0!"));
                        }
                    } else {
                        foreach ($params['update_paid'] as $updatePaid) {
                            if ($updatePaid) {
                                $this->messageManager->addWarningMessage(__('You cannot change "Update Credit" config for specific order when updating simultaneously with "Update Available Credit" config.'));
                                break;
                            }
                        }
                    }
                }

                if ((!$creditLimitParam && $creditLimitParam !== "0") && $action == "massUpdateCredit") {
                    $creditLimitParam = $creditLimitOld;
                }
                if ((!$allowExceed && $allowExceed !== "0") && $action == "massUpdateCredit") {
                    $allowExceed = $credit->getAllowExceed();
                }

                $availableCreditChange = $creditLimitParam - $creditLimitOld + (float)($availableCreditParam);
            } else {
                if ($allowExceed === null) {
                    $allowExceed = 1;
                }

                $availableCreditChange = (float)$creditLimitParam - $creditLimitOld;
                $checkFirst = 1;
                $credit = $this->creditFactory->create();
            }
            $dataEmail = [
                "store_id" => $customer->getStoreId(),
                "customer_email" => $customer->getEmail(),
                "website_id" => $customer->getWebsiteId(),
                "variables" => [
                    "customer_name" => $customerName,
                    'comment' => $comment,
                ]
            ];
            $availableCredit = $availableCreditChange + $availableCreditOld;
            $usedCredit = (float)$creditLimitParam - $availableCredit;
            if ($usedCredit >= 0 && ($allowExceed || ($availableCredit >= 0 && $availableCredit <= $creditLimitParam))) {
                if ((
                    $checkFirst || $params['update_available'] ||
                    $params['credit_limit'] != $creditLimitOld
                    || $allowExceedOld != $allowExceed
                    || $paymentDueDateOld != $paymentDueDate
                    || !empty($changeSingleCredit)
                )) {
                    if (!$this->authorization->isAllowed("Bss_CompanyCredit::saveCompanyCredit")) {
                        $this->messageManager->addWarningMessage(__("Sorry, you need permissions to save company credit"));
                        return;
                    }
                    $currencyCode = $this->helperCurrency->getCurrencyCodeByWebsite($customer->getWebsiteId());
                    $dataHistory = [
                        'customer_id' => $customerId,
                        'website_id' => 0,
                        'type' => History::TYPE_ADMIN_REFUND,
                        'change_credit' => $availableCreditChange,
                        'available_credit_current' => $availableCredit,
                        'comment' => $comment,
                        'allow_exceed' => $allowExceed,
                        'po_number' => null,
                        'currency_code' => $currencyCode
                    ];

                    if ($action === "massUpdateCredit") {
                        $paymentDueDate = $paymentDueDate ?: $paymentDueDateOld;
                    } else {
                        $paymentDueDate = $paymentDueDate ?: null;
                    }

                    $credit->setAvailableCredit($availableCredit);
                    $credit->setUsedCredit($usedCredit);
                    $credit->setCreditLimit($creditLimitParam);
                    $credit->setAllowExceed($allowExceed);
                    $credit->setPaymentDueDate($paymentDueDate);
                    $credit->setCustomerId($customerId);
                    $credit->setCurrencyCode($currencyCode);
                    $this->companyCreditRepository->save($credit);

                    if (empty($changeSingleCredit)) {
                        $updateCreditDue = $paymentDueDateOld != $paymentDueDate;
                        $this->updatePaymentStatus->execute($credit, $availableCreditParam, $updateCreditDue);
                    }

                    if (!$action) {
                        $this->messageManager
                            ->addSuccessMessage(__("You have successfully saved changes to company credit."));
                    } else {
                        $dataMessage["success"] = "You have successfully saved changes to company credit.";
                    }
                    if (!$checkFirst
                        && !empty($changeSingleCredit)
                        && isset($totalChangeCredit)
                        && !empty($commentChangeCredit)
                    ) {
                        $this->updateValueSingleCredit(
                            $totalChangeCredit,
                            $commentChangeCredit,
                            $dataHistory,
                            $availableCredit,
                            $availableCreditOld,
                            $dataEmail,
                            $historyModel
                        );
                    }
                    if (!$checkFirst && $params['update_available']) {
                        $this->updateCreditValue(
                            $params,
                            $dataHistory,
                            $availableCredit,
                            $availableCreditOld,
                            $dataEmail,
                            $historyModel
                        );
                    }
                    if ($checkFirst || ($creditLimitParam != $creditLimitOld)) {
                        $this->changeCreditLimit($params, $dataHistory, $creditLimitOld, $dataEmail, $historyModel);
                    }
                    if ($allowExceedOld != $allowExceed || $checkFirst) {
                        $this->allowExceedCredit($dataHistory, $historyModel);
                    }
                }
            } else {
                if (!$this->authorization->isAllowed("Bss_CompanyCredit::saveCompanyCredit")) {
                    $this->messageManager->addWarningMessage(__("Sorry, you need permissions to save company credit"));
                    return;
                }
                if (!$action) {
                    $this->messageManager->addErrorMessage(__("You cannot update available credit to greater than credit limit."));
                } else {
                    array_push($dataMessage["error"], $customerId);
                }
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }

    /**
     * Save credit limit and send email for customer
     *
     * @param array $params
     * @param array $dataHistory
     * @param float $creditLimitOld
     * @param array $dataEmail
     * @param History $historyModel
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function changeCreditLimit($params, $dataHistory, $creditLimitOld, $dataEmail, $historyModel)
    {
        $dataHistory["type"] = History::TYPE_ADMIN_CHANGES_CREDIT_LIMIT;
        $dataHistory["change_credit"] = $params['credit_limit'] - $creditLimitOld;
        $historyModel->updateHistory($dataHistory);
        $this->historyRepository->save($historyModel);
        $dataEmail["variables"]["old_value"] = $this->helperCurrency->formatPrice($creditLimitOld, $dataHistory["currency_code"]);
        $dataEmail["variables"]["new_value"] = $this->helperCurrency->formatPrice((float)$params['credit_limit'], $dataHistory["currency_code"]);
        $this->helperEmail->sendEmailAdmin($dataEmail, "sendChangeCreditLimit");
    }

    /**
     * Save update credit value and send email for customer
     *
     * @param float $totalChangeCredit
     * @param string $commentChangeCredit
     * @param array $dataHistory
     * @param float $availableCredit
     * @param float $availableCreditOld
     * @param array $dataEmail
     * @param History $historyModel
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function updateValueSingleCredit($totalChangeCredit, $commentChangeCredit, $dataHistory, $availableCredit, $availableCreditOld, $dataEmail, $historyModel)
    {
        $dataEmail["variables"]["old_value"] = $this->helperCurrency->formatPrice($availableCreditOld, $dataHistory["currency_code"]);
        $dataEmail["variables"]["new_value"] = $this->helperCurrency->formatPrice($availableCredit, $dataHistory["currency_code"]);
        $dataHistory["change_credit"] = $totalChangeCredit;
        $dataHistory["available_credit_current"] = $availableCreditOld + $totalChangeCredit;
        $dataHistory["comment"] = $commentChangeCredit ? rtrim($commentChangeCredit, ", ") : "";
        $historyModel->updateHistory($dataHistory);
        $this->historyRepository->save($historyModel);
        $this->helperEmail->sendEmailAdmin($dataEmail, "sendUpdateCreditValue");
    }

    /**
     * Save update credit value and send email for customer
     *
     * @param array $params
     * @param array $dataHistory
     * @param float $availableCredit
     * @param float $availableCreditOld
     * @param array $dataEmail
     * @param History $historyModel
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function updateCreditValue($params, $dataHistory, $availableCredit, $availableCreditOld, $dataEmail, $historyModel)
    {
        $dataEmail["variables"]["old_value"] = $this->helperCurrency->formatPrice($availableCreditOld, $dataHistory["currency_code"]);
        $dataEmail["variables"]["new_value"] = $this->helperCurrency->formatPrice($availableCredit, $dataHistory["currency_code"]);
        $dataHistory["change_credit"] = $params['update_available'];
        $dataHistory["available_credit_current"] = $availableCreditOld + $params['update_available'];
        $historyModel->updateHistory($dataHistory);
        $this->historyRepository->save($historyModel);
        $this->helperEmail->sendEmailAdmin($dataEmail, "sendUpdateCreditValue");
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
     * Log message error
     *
     * @param string $message
     */
    public function logError($message)
    {
        $this->_logger->critical($message);
    }
}
