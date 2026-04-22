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

use Bss\CompanyCredit\Api\SaveInterface;
use Bss\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollection;
use Magento\Framework\Exception\InputException;

class Save implements SaveInterface
{
    /**
     * @var \Bss\CompanyCredit\Helper\Api
     */
    protected $helperApi;

    /**
     * @var HistoryCollection
     */
    protected $historyCollection;

    /**
     * Save constructor.
     *
     * @param \Bss\CompanyCredit\Helper\Api $helperApi
     * @param HistoryCollection $historyCollection
     */
    public function __construct(
        \Bss\CompanyCredit\Helper\Api $helperApi,
        HistoryCollection $historyCollection
    ) {
        $this->helperApi = $helperApi;
        $this->historyCollection = $historyCollection;
    }

    /**
     * Save company credit
     *
     * @param string[] $saveCompanyCredit
     * @return mixed
     * @throws InputException
     */
    public function save(
        $saveCompanyCredit
    ) {
        $this->validate($saveCompanyCredit, "validateSave");
        $saveCompanyCredit["baseCurrencyCode"] = "";
        return $this->helperApi->save($saveCompanyCredit);
    }

    /**
     * Save company credit
     *
     * @param string[] $saveCompanyCredit
     * @return mixed
     * @throws InputException
     */
    public function saveDirectAvaliableCredit(
        $saveCompanyCredit
    ) {
        $this->validate($saveCompanyCredit, "validateSaveDirectAvailableCredit");
        $saveCompanyCredit["baseCurrencyCode"] = "";
        return $this->helperApi->saveDirectAvaliableCredit($saveCompanyCredit);
    }

    /**
     * Validate params input
     *
     * @param array $saveCompanyCredit
     * @param string $type
     * @throws InputException
     */
    public function validate($saveCompanyCredit, $type)
    {
        if (!isset($saveCompanyCredit["customer_id"])) {
            throw new InputException(__('Input customer_id is required. Enter and try again.'));
        }

        $this->$type($saveCompanyCredit);

        if (isset($saveCompanyCredit["credit_limit"]) && $saveCompanyCredit["credit_limit"] < 0) {
            throw new InputException(
                __("Please validate input credit_limit >= 0")
            );
        }

        if (isset($saveCompanyCredit["order_id"])) {
            $this->validateOrderId($saveCompanyCredit);
        }
    }

    /**
     * Validate params input
     *
     * @param array $saveCompanyCredit
     * @throws InputException
     */
    public function validateSave($saveCompanyCredit)
    {
        if (!isset($saveCompanyCredit["credit_limit"]) &&
            !isset($saveCompanyCredit["update_available"]) && !isset($saveCompanyCredit["allow_exceed"])
        ) {
            throw new InputException(__('You must provide at least one of the following values: credit_limit, update_available, allow_exceed.'));
        }
    }

    /**
     * Validate params input
     *
     * @param array $saveCompanyCredit
     * @throws InputException
     */
    public function validateSaveDirectAvailableCredit($saveCompanyCredit)
    {
        if (!isset($saveCompanyCredit["credit_limit"]) &&
            !isset($saveCompanyCredit["available_credit"]) && !isset($saveCompanyCredit["allow_exceed"])
        ) {
            throw new InputException(__('You must provide at least one of the following values: credit_limit, update_available, allow_exceed.'));
        }
    }

    /**
     * Validate input Order ID
     *
     * @param array $saveCompanyCredit
     * @throws InputException
     */
    public function validateOrderId($saveCompanyCredit)
    {
        if ($saveCompanyCredit["order_id"] <= 0) {
            throw new InputException(
                __("Please validate value input order_id > 0")
            );
        }
        $historyCollection = $this->historyCollection->create()
            ->addFieldToFilter("order_id", $saveCompanyCredit["order_id"]);
        if ($historyCollection->getSize()) {
            throw new InputException(
                __("You can't update credit. Because order_id exist")
            );
        }
    }
}
