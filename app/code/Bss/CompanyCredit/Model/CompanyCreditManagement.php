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
namespace Bss\CompanyCredit\Model;

use Bss\CompanyCredit\Api\CompanyCreditManagementInterface;
use Bss\CompanyCredit\Helper\Data as HelperData;
use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Model\HistoryFactory;

class CompanyCreditManagement implements CompanyCreditManagementInterface
{
    /**
     * @var CreditRepositoryInterface
     */
    protected $creditRepository;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HistoryFactory
     */
    protected $history;

    /**
     * CompanyCreditManagement constructor.
     *
     * @param HelperData $helperData
     * @param CreditRepositoryInterface $creditRepository
     * @param HistoryFactory $history
     */
    public function __construct(
        HelperData $helperData,
        CreditRepositoryInterface $creditRepository,
        HistoryFactory $history
    ) {
        $this->helperData = $helperData;
        $this->creditRepository = $creditRepository;
        $this->history = $history;
    }

    /**
     * Get config module
     *
     * @param int $websiteId
     * @return array
     */
    public function getConfig($websiteId = null)
    {
        $result["module_configs"]["enable"] = $this->helperData->isEnableModule($websiteId);
        return $result;
    }

    /**
     * Get Credit by customer id
     *
     * @param int $customerId
     * @return mixed
     */
    public function getCredit($customerId)
    {
        $result = null;
        $credit = $this->creditRepository->get($customerId);
        if ($credit && $credit->getId()) {
            $result["credit"] = $credit->getData();
        }
        return $result;
    }

    /**
     * Get History Credit by customer id
     *
     * @param int $customerId
     * @return mixed|void
     */
    public function getCreditHistory($customerId)
    {
        $result = null;
        try {
            $historyCollection = $this->history->create()->loadByCustomer($customerId);
            if ($historyCollection && $historyCollection->getSize()) {
                $historyAll = $historyCollection->getData();
                foreach ($historyAll as $key => $history) {
                    $historyAll[$key]["type"] =
                        __($this->helperData->getTypeAction($history["type"], $history["allow_exceed"]));
                }
                $result = $historyAll;
            }
        } catch (\Exception $exception) {
            return (__($exception->getMessage()));
        }

        return $result;
    }
}
