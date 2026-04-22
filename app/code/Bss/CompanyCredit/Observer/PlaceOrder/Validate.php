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

namespace Bss\CompanyCredit\Observer\PlaceOrder;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Helper\Currency;
use Bss\CompanyCredit\Helper\Data;
use Bss\CompanyCredit\Helper\Email;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class Validate Bss.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Validate implements ObserverInterface
{
    /**
     * @var CreditRepositoryInterface
     */
    private $companyCreditRepository;

    /**
     * @var Email
     */
    protected $helperEmail;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Currency
     */
    protected $helperCurrency;

    /**
     * Order constructor.
     *
     * @param CreditRepositoryInterface $companyCreditRepository
     * @param Email $helperEmail
     * @param Data $helperData
     * @param Currency $helperCurrency
     */
    public function __construct(
        CreditRepositoryInterface $companyCreditRepository,
        Email $helperEmail,
        Data $helperData,
        Currency $helperCurrency
    ) {
        $this->companyCreditRepository = $companyCreditRepository;
        $this->helperEmail = $helperEmail;
        $this->helperData = $helperData;
        $this->helperCurrency = $helperCurrency;
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
        $error = 0;
        try {
            if ($this->helperData->isEnableModule()) {
                $order = $observer->getEvent()->getOrder();
                if ($order && $order->getPayment()) {
                    $payment = $order->getPayment();
                    if ($payment->getMethod() == "purchaseorder") {
                        $customerId = $order->getCustomerId();
                        $companyCredit = $this->companyCreditRepository->get($customerId);
                        if ($companyCredit && $companyCredit->getId()) {
                            $currencyCodeCredit = $companyCredit->getCurrencyCode();
                            $currencyCodeQuote = $order->getBaseCurrencyCode();
                            $baseOrderTotal = $this->helperCurrency
                                ->convertCurrency($order->getBaseGrandTotal(), $currencyCodeQuote, $currencyCodeCredit);
                            $availableCreditNew = $companyCredit->getAvailableCredit() - $baseOrderTotal;
                            if ($availableCreditNew < 0 && !$companyCredit->getAllowExceed()) {
                                $error = 1;
                            }
                        }
                    }
                }
            }
        } catch (Exception $exception) {
            $this->helperData->logError($exception->getMessage());
        }
        if ($error) {
            throw new CouldNotSaveException(
                __("Sorry, you cannot place order at the moment.")
            );
        }
    }
}
