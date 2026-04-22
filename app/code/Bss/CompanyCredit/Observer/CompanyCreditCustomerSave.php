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

use Bss\CompanyCredit\Helper\Model as HelperModel;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CompanyCreditCustomerSave implements ObserverInterface
{
    /**
     * @var HelperModel
     */
    protected $helperModel;

    /**
     * CompanyCreditCustomerSave constructor
     *
     * @param HelperModel $helperModel
     */
    public function __construct(
        HelperModel $helperModel
    ) {
        $this->helperModel = $helperModel;
    }

    /**
     * Credit update for customer after save
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();
        $params = $observer->getRequest()->getParams();
        $customerId = $customer->getId();

        if (isset($params['bss_companycredit']) && $customerId &&
            isset($params['bss_companycredit']["credit_limit"]) &&
            is_numeric($params['bss_companycredit']["credit_limit"])
        ) {
            $this->helperModel->saveCompanyCredit($customer, $params["bss_companycredit"]);
        }
    }
}
