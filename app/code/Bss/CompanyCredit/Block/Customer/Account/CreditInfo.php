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
namespace Bss\CompanyCredit\Block\Customer\Account;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Bss\CompanyCredit\Helper\Data as CompanyCreditData;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CreditInfo extends Template
{
    /**
     * @var HelperCurrency
     */
    protected $helperCurrency;

    /**
     * @var \Bss\CompanyCredit\Api\CreditRepositoryInterface
     */
    private $creditRepository;

    /**
     * @var \Bss\CompanyCredit\Helper\Data
     */
    private $helperData;

    /**
     * CreditInfo Construct
     *
     * @param HelperCurrency $helperCurrency
     * @param Context $context
     * @param CompanyCreditData $helperData
     * @param CreditRepositoryInterface $creditRepository
     * @param array $data
     */
    public function __construct(
        HelperCurrency $helperCurrency,
        Context $context,
        CompanyCreditData $helperData,
        CreditRepositoryInterface $creditRepository,
        array $data = []
    ) {
        $this->helperCurrency = $helperCurrency;
        $this->creditRepository = $creditRepository;
        parent::__construct($context, $data);
        $this->helperData = $helperData;
    }

    /**
     * Get balance given the customer Id stored in the session.
     *
     * @return null|array
     */
    public function getDataCompanyCredit()
    {
        $credit = $this->creditRepository->get();
        $currencyCode = $credit->getCurrencyCode();
        if ($credit->getId()) {
            return [
                "credit_limit" => $this->convertPrice($credit->getCreditLimit(), $currencyCode),
                "used_credit" => $this->convertPrice($credit->getUsedCredit(), $currencyCode),
                "available_credit" => $this->convertPrice($credit->getAvailableCredit(), $currencyCode),
                "allow_exceed" => $this->convertYesNo($credit->getAllowExceed())
            ];
        }
        return null;
    }

    /**
     * Convert price with currency
     *
     * @param float $price
     * @param string $currencyCode
     * @return float|string
     */
    public function convertPrice($price, $currencyCode)
    {
        $currencyCodeWebsite = $this->helperCurrency->getCurrencyCodeByWebsite();
        $price = $this->helperCurrency->convertCurrency($price, $currencyCode, $currencyCodeWebsite);
        return $this->helperCurrency->currency($price, true, false);
    }

    /**
     * Convert Yes or No
     *
     * @param string $value
     * @return Phrase
     */
    public function convertYesNo($value)
    {
        return $this->helperData->convertYesNo($value);
    }
}
