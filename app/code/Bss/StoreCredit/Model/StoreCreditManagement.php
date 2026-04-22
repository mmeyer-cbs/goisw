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

use Bss\StoreCredit\Api\Data\HistoryInterface;
use Bss\StoreCredit\Api\Data\StoreCreditInterface;
use Bss\StoreCredit\Api\HistoryRepositoryInterface;
use Bss\StoreCredit\Api\StoreCreditManagementInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class StoreCreditManagement
 *
 * @package Bss\StoreCredit\Model
 */
class StoreCreditManagement implements StoreCreditManagementInterface
{
    /**
     * @var HistoryFactory
     */
    protected $history;

    /**
     * @var \Bss\StoreCredit\Helper\Api
     */
    protected $helperApi;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var HistoryRepositoryInterface
     */
    protected $historyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    protected $storeCreditRepository;

    /**
     * StoreCreditManagement constructor.
     *
     * @param HistoryFactory $history
     * @param \Bss\StoreCredit\Helper\Api $helperApi
     * @param \Psr\Log\LoggerInterface $logger
     * @param HistoryRepositoryInterface $historyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Bss\StoreCredit\Helper\Data $helperData
     * @param \Bss\StoreCredit\Api\StoreCreditRepositoryInterface $storeCreditRepository
     */
    public function __construct(
         HistoryFactory $history,
        \Bss\StoreCredit\Helper\Api $helperApi,
        \Psr\Log\LoggerInterface $logger,
        \Bss\StoreCredit\Api\HistoryRepositoryInterface $historyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Bss\StoreCredit\Helper\Data $helperData,
        \Bss\StoreCredit\Api\StoreCreditRepositoryInterface $storeCreditRepository
    ) {
        $this->history = $history;
        $this->helperApi = $helperApi;
        $this->logger = $logger;
        $this->historyRepository = $historyRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->helperData = $helperData;
        $this->storeCreditRepository = $storeCreditRepository;
    }

    /**
     * Get config module by store Id
     *
     * @param int $storeId
     * @return array
     */
    public function getConfig($storeId)
    {
        $result["module_configs"] = [
            "enable" =>  $this->helperData->getGeneralConfig("active"),
            "checkout_page_display" => $this->helperData->getGeneralConfig("checkout_page_display"),
            "cart_page_display" => $this->helperData->getGeneralConfig("cart_page_display"),
            "used_shipping" => $this->helperData->getGeneralConfig("used_shipping"),
            "used_tax" => $this->helperData->getGeneralConfig("cart_page_display")
        ];
        return $result;
    }

    /**
     * Get Credit by customer id
     *
     * @param int $customerId
     * @param int $websiteId
     * @return array|null
     */
    public function getCredit($customerId, $websiteId)
    {
        $credit = $this->storeCreditRepository->get($customerId, $websiteId);
        if ($credit && $credit->getId()) {
            return ["credit" => $credit->getData()];
        }
        return null;
    }


    /**
     * Get all Credit by customer ID
     *
     * @param int $customerId
     * @return StoreCreditInterface[]
     */
    public function getAllCreditCustomerId($customerId)
    {
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter("customer_id", $customerId);
        $searchCriteria = $searchCriteriaBuilder->create();
        $allCredit = $this->storeCreditRepository->getList($searchCriteria);
        return $allCredit->getItems();
    }

    /**
     * Get all history credit by customer ID
     *
     * @param int $customerId
     * @return HistoryInterface[]|ExtensibleDataInterface[]
     */
    public function getAllHistoryCreditCustomerId($customerId)
    {
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter("customer_id", $customerId);
        $searchCriteria = $searchCriteriaBuilder->create();
        $history = $this->historyRepository->getList($searchCriteria);
        return $this->helperApi->getHistoryItemFormatDate($history);
    }

    /**
     * Get History by id
     *
     * @param int $historyId
     * @return array|null
     */
    public function getHistoryById($historyId)
    {
        try {
            $history = $this->historyRepository->getById($historyId);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return null;
        }
        if ($history->getId()) {
            $history->setCreatedTime($this->helperApi->getOutputTime($history->getCreatedTime()));
            $history->setUpdatedTime($this->helperApi->getOutputTime($history->getUpdatedTime()));
            return ["history" => $history->getData()];
        }
        return null;
    }

    /**
     * Get history in interval
     *
     * @param string $startDate
     * @param string $endDate
     * @return HistoryInterface[]|ExtensibleDataInterface[]
     * @throws \Exception
     */
    public function getHistoryInInterval($startDate, $endDate) {
        $startDate = $this->helperApi->getFromTo($startDate);
        $endDate = $this->helperApi->getToDate($endDate);
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter("updated_time", $startDate, "gteq")
                                                       ->addFilter("updated_time", $endDate,  "lteq");
        $searchCriteria = $searchCriteriaBuilder->create();
        $history = $this->historyRepository->getList($searchCriteria);
        return $this->helperApi->getHistoryItemFormatDate($history);
    }

    /**
     * Get report interval
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $period
     * @return \Bss\StoreCredit\Api\Data\HistoryInterface[]|\Magento\Framework\Api\ExtensibleDataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReport($startDate, $endDate, $period) {
        return $this->history->create()->loadReportDataApi($startDate, $endDate, $period);
    }
}
