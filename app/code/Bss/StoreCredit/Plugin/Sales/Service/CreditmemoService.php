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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Plugin\Sales\Service;

use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Class CreditmemoService
 * @package Bss\StoreCredit\Plugin\Sales\Service
 */
class CreditmemoService
{
    /**
     * @param CreditmemoInterface $creditmemo
     * @param bool $offlineRequested
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRefund(
        \Magento\Sales\Model\Service\CreditmemoService $subject,
        CreditmemoInterface $creditmemo,
        $offlineRequested = false
    ) {
//        $order = $creditmemo->getOrder();
//        if ($baseStoreCredit = $order->getData('base_bss_storecredit_amount')) {
//            $baseTotalPaid = $order->getBaseTotalPaid();
//            $order->setBaseTotalPaid($baseTotalPaid + $baseStoreCredit);
//        }
//
//        if ($storeCredit = $order->getData('bss_storecredit_amount')) {
//            $totalPaid = $order->getTotalPaid();
//            $order->setTotalPaid($totalPaid + $storeCredit);
//        }
//
//        $creditmemo->setOrder($order);

        return [$creditmemo,$offlineRequested];
    }
}
