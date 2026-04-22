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
namespace Bss\SalesRep\Helper;

use Bss\SalesRep\Model\OrderCreatedByAdminFactory;
use Bss\SalesRep\Model\ResourceModel\OrderCreatedByAdmin as OrderCreatedByAdminResource;
use Bss\SalesRep\Model\ResourceModel\OrderCreatedByAdmin\CollectionFactory as OrderCreatedByAdminCollection;
use Bss\SalesRep\Model\ResourceModel\SalesOrder\CollectionFactory as SalesOrderCollection;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * @package Bss\SalesRep\Helper
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class OrderCreatedByAdmin extends AbstractHelper
{

    /**
     * @var OrderCreatedByAdminResource
     */
    protected $orderCreatedByAdminResource;

    /**
     * @var SalesOrderCollection
     */
    protected $salesOrderCollection;

    /**
     * @var OrderCreatedByAdminFactory
     */
    protected $orderCreatedByAdmin;

    /**
     * @var OrderCreatedByAdminCollection
     */
    protected $orderCreatedByAdminCollection;

    /**
     * OrderCreatedByAdmin constructor.
     *
     * @param OrderCreatedByAdminResource $orderCreatedByAdminResource
     * @param SalesOrderCollection $salesOrderCollection
     * @param OrderCreatedByAdminFactory $orderCreatedByAdmin
     * @param OrderCreatedByAdminCollection $orderCreatedByAdminCollection
     * @param Context $context
     */
    public function __construct(
        OrderCreatedByAdminResource $orderCreatedByAdminResource,
        SalesOrderCollection $salesOrderCollection,
        OrderCreatedByAdminFactory $orderCreatedByAdmin,
        OrderCreatedByAdminCollection $orderCreatedByAdminCollection,
        Context $context
    ) {
        $this->orderCreatedByAdminResource = $orderCreatedByAdminResource;
        $this->salesOrderCollection = $salesOrderCollection;
        $this->orderCreatedByAdmin = $orderCreatedByAdmin;
        $this->orderCreatedByAdminCollection = $orderCreatedByAdminCollection;
        parent::__construct($context);
    }

    /**
     * Save data into table order_created_by_admin
     *
     * @param string $orderId
     * @param string $createdByAdmin
     */
    public function saveTableOrderCreatedByAdmin($orderId, $createdByAdmin)
    {
        if ($orderId) {
            try {
                $orderAdminCollection = $this->orderCreatedByAdminCollection->create()
                    ->addFieldToFilter('order_id', $orderId)->getLastItem();
                if ($orderAdminCollection->getData()) {
                    $orderAdminCollection->setCreatedByAdminOrder($createdByAdmin);
                    $orderAdminCollection->save();
                } else {
                    $orderAdmin = $this->orderCreatedByAdmin->create();
                    $orderAdmin->setCreatedByAdmin($createdByAdmin);
                    $orderAdmin->setOrderId($orderId);
                    $orderAdmin->save();
                }
            } catch (Exception $exception) {
                $this->_logger->critical($exception->getMessage());
            }
        }
    }

    /**
     * Save data table order_created_by_admin from table sales_order
     *
     * @param string $action
     * @return int|void|null
     */
    public function saveOrderAdminFromSalesOrder($action = null)
    {
        try {
            $data = [];
            $salesOrderCollection = $this->salesOrderCollection->create();
            if ($action == "update") {
                $orderId = $this->orderCreatedByAdminCollection->create()
                    ->getLastItem()->getOrderId();
                $salesOrderCollection = $this->salesOrderCollection->create()
                    ->addFieldToFilter('entity_id', ["gt" => $orderId]);
            }
            foreach ($salesOrderCollection as $key => $item) {
                if ($item["entity_id"]) {
                    $data[$key]["order_id"] = $item["entity_id"];
                    if ($item["remote_ip"]) {
                        $data[$key]["created_by_admin"] = 0;
                    } else {
                        $data[$key]["created_by_admin"] = 1;
                    }
                }
            }
            if (count($data) > 0) {
                return $this->orderCreatedByAdminResource->insertMultiple($data);
            }
        } catch (Exception $exception) {
            return $this->_logger->critical($exception->getMessage());
        }
    }
}
