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

namespace Bss\StoreCredit\Plugin\Model;

use Bss\StoreCredit\Helper\Data;
use Bss\StoreCredit\Model\ResourceModel\History\Collection;

/**
 * Class Grid
 * @package Bss\StoreCredit\Plugin\Adminhtml\Order\Create\Items
 */
class OrderRepository
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Collection
     */
    protected $historyCollection;


    /**
     * @param Data $helperData
     * @param Collection $historyCollection
     */
    public function __construct(
        \Bss\StoreCredit\Helper\Data                            $helperData,
        \Bss\StoreCredit\Model\ResourceModel\History\Collection $historyCollection
    ) {
        $this->helperData = $helperData;
        $this->historyCollection = $historyCollection;
    }

    /**
     * @param \Magento\Sales\Model\OrderRepository $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $result
     * @return mixed
     */
    public function afterGetList(
        $subject,
        $result
    ) {
        $orders = $result->getItems();

        foreach ($orders as $order) {
            if (isset($order['bss_storecredit_amount']) && $order['bss_storecredit_amount']) {
                $this->helperData->addStoreCreditToExtensionAttrOrder($order);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Sales\Model\OrderRepository $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $result
     * @return mixed
     */
    public function afterGet(
        $subject,
        $result
    ) {
        $this->helperData->addStoreCreditToExtensionAttrOrder($result);
        return $result;
    }
}
