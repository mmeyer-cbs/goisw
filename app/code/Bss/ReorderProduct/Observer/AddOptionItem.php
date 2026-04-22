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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddOptionItem
 *
 * @package Bss\ReorderProduct\Observer
 */
class AddOptionItem implements ObserverInterface
{
    /**
     * @var \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions
     */
    protected $saveReorderItemOptions;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @var \Bss\ReorderProduct\Model\SaveItemOptions
     */
    protected $saveItemOptions;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * AddOptionItem constructor.
     * @param \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions $saveReorderItemOptions
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Bss\ReorderProduct\Model\SaveItemOptions $saveItemOptions
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions $saveReorderItemOptions,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Bss\ReorderProduct\Model\SaveItemOptions $saveItemOptions,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->saveReorderItemOptions = $saveReorderItemOptions;
        $this->serializer = $serializer;
        $this->saveItemOptions = $saveItemOptions;
        $this->logger = $logger;
    }

    /**
     * Process save option when placed order
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orders = $observer->getEvent()->getOrders(); // case Checkout with Multi Addresses

        if ($order != null) {
            $this->saveItemOptions->processData($order);
        }

        if ($orders != null) {
            foreach ($orders as $order) {
                $this->saveItemOptions->processData($order);
            }
        }
    }
}
