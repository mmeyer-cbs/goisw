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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\CustomerAttributes\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BeforeAddressSaveObserverFE implements ObserverInterface
{
    /**
     * Address before save event handler
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $saveData = $observer->getCustomerAddress()->getData();
        foreach ($saveData as $key => $value) {
            if ($value === false) {
                unset($saveData[$key]);
            }
        }
    }
}
