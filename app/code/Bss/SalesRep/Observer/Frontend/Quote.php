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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Observer\Frontend;

use Exception;
use Magento\Framework\Event\Observer;

/**
 * Class Order
 *
 * @package Bss\SalesRep\Observer\Frontend
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Quote extends \Bss\SalesRep\Observer\Frontend\Order
{
    /**
     * Set User Id
     *
     * @param Observer $observer
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $action = $this->request->getActionName();
            $notAction = ['edit', 'sendCustomer', 'rejected'];
            $listModule = ['sales', 'admin'];
            $module = $this->request->getModuleName();
            if (!in_array($action, $notAction) && !in_array($module, $listModule)) {
                $data = $observer->getDataObject();
                $customerId = $data->getCustomerId();
                $customerCollection = $this->customer->load($customerId);
                $user_id = '';
                if (isset($customerCollection['bss_sales_representative'])) {
                    $user_id = $customerCollection['bss_sales_representative'];
                }
                $data->setUserId($user_id);
            }
        }
    }
}
