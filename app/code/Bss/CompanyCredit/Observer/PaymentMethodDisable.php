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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\CompanyCredit\Observer;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Helper\Data as HelperData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;

class PaymentMethodDisable implements ObserverInterface
{
    /**
     *
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @var Session
     */
    protected $customer;

    /**
     * @var CreditRepositoryInterface
     */
    protected $creditRepository;

    /**
     * @var HelperData
     */
    protected $helperData;


    public function __construct(
        RequestInterface          $requestInterface,
        Session                   $customer,
        HelperData                $helperData,
        CreditRepositoryInterface $creditRepository
    ) {
        $this->requestInterface = $requestInterface;
        $this->customer = $customer;
        $this->helperData = $helperData;
        $this->creditRepository = $creditRepository;
    }

    /**
     * Execute function hide purchase order if multishipping address
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $enableModule = $this->helperData->isEnableModule();
        if ($enableModule){
            $customer = $this->customer;
            $customerId = $customer->getCustomerId();
            if ($customerId) {
                $credit = $this->creditRepository->get($customerId);
                if (!empty($credit->getData()) &&
                    $this->requestInterface->getModuleName() == 'multishipping' &&
                    $observer->getEvent()->getMethodInstance()->getCode() == "purchaseorder"
                ) {
                    $checkResult = $observer->getEvent()->getResult();
                    $checkResult->setData('is_available', false);
                }
            }
        }
    }
}
