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
namespace Bss\ReorderProduct\Model\ResourceModel;

/**
 * Class Import
 *
 * @package Bss\ReorderProduct\Model\ResourceModel
 */
class Import
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resources;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $itemOrderCollectionFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\ReorderProduct\Helper\HelperClass
     */
    protected $helperClass;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\ResourceConnection $resources
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemOrderCollectionFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\ReorderProduct\Helper\HelperClass $helperClass
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resources,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemOrderCollectionFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Psr\Log\LoggerInterface $logger,
        \Bss\ReorderProduct\Helper\HelperClass $helperClass
    ) {
        $this->resources = $resources;
        $this->itemOrderCollectionFactory = $itemOrderCollectionFactory;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->helperClass = $helperClass;
    }

    /**
     * Count order item collection
     *
     * @return int
     */
    public function getNumberItem()
    {
        $collection = $this->itemOrderCollectionFactory->create();
        return $collection->getSize();
    }

    /**
     * { @inheritdoc}
     */
    public function clear()
    {
        $connection = $this->resources->getConnection();
        $reorderItem = $this->resources->getTableName('bss_reorder_item_options');
        $connection->truncateTable($reorderItem);
    }

    /**
     * Import order to reorder table
     *
     * @param int $start
     * @param int $numberRowEachrun
     * @throws \Zend_Db_Statement_Exception
     */
    public function import($start, $numberRowEachrun)
    {
        $connection = $this->resources->getConnection();
        $order_item = $this->resources->getTableName('sales_order_item');
        $reorder_item = $this->resources->getTableName('bss_reorder_item_options');
        $dataImp = [];
        $sql = $connection->select()
            ->from(['oi'=> $order_item], ['item_id','product_options','product_id'])
            ->limit($numberRowEachrun, $start);
        $result = $connection->query($sql);
        while ($row = $result->fetch()) {
            $itemOptionValue = $this->helperClass->returnJsonHelper()->jsonDecode($row['product_options']);
            if (isset($itemOptionValue['info_buyRequest'])) {
                $itemOptionValue['info_buyRequest'] = $this->returnInfoBuyRequest($row, $itemOptionValue);
                foreach (['qty', 'uenc', 'form_key', 'item'] as $key) {
                    unset($itemOptionValue['info_buyRequest'][$key]);
                }
                $newItemOptionValue = array_filter($itemOptionValue['info_buyRequest'], function ($optionValue) {
                    return !empty($optionValue);
                });
                $dataImp[] = [
                    'item_id'=>$row['item_id'],
                    'item_options' => $this->serializer->serialize($newItemOptionValue)
                ];
            } else {
                $dataImp[] = ['item_id'=>$row['item_id'], 'item_options' => ''];
            }
        }

        if (!empty($dataImp)) {
            try {
                $connection->insertMultiple($reorder_item, $dataImp);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }

    /**
     * Get info buy request
     *
     * @param int $row
     * @param array $itemOptionValue
     * @return mixed
     */
    private function returnInfoBuyRequest($row, $itemOptionValue)
    {
        if (!isset($itemOptionValue['info_buyRequest']['product'])) {
            $product['product'] = $row['product_id'];
            $itemOptionValue['info_buyRequest'] = $product + $itemOptionValue['info_buyRequest'];
        }

        return $itemOptionValue['info_buyRequest'];
    }
}
