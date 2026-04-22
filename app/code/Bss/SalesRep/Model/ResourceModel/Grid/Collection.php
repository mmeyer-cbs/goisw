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

namespace Bss\SalesRep\Model\ResourceModel\Grid;

use Bss\SalesRep\Helper\Data as HelperData;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 *
 * @package Bss\SalesRep\Model\ResourceModel\Grid
 */
class Collection extends Order\Grid\Collection
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Collection constructor.
     * @param HelperData $helperData
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        HelperData $helperData,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = Order::class
    ) {
        $this->helperData = $helperData;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Filter Sale Rep
     *
     * @return $this|SearchResult|Order\Grid\Collection|void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('sales_rep', 'admin_user.username');
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['bss_sales_rep_order' => $this->getTable('bss_sales_rep_order')],
            'main_table.entity_id = bss_sales_rep_order.order_id',
            ['user_id' => 'bss_sales_rep_order.user_id']
        )->joinLeft(
            ['admin_user' => $this->getTable('admin_user')],
            'bss_sales_rep_order.user_id = admin_user.user_id',
            ['sales_rep' => 'admin_user.username']
        );
        if ($this->helperData->isEnableCompanyAccount()) {
            $this->getSelect()->joinLeft(
                ['bss_sub_user_order' => $this->getTable('bss_sub_user_order')],
                'bss_sales_rep_order.order_id = bss_sub_user_order.order_id',
                ['sub_id' => 'bss_sub_user_order.sub_id']
            )
                ->joinLeft(
                    ['bss_sub_user' => $this->getTable('bss_sub_user')],
                    'bss_sub_user.sub_id = bss_sub_user_order.sub_id',
                    ['sub_name' => 'bss_sub_user.sub_name']
                )->distinct(true);
        }
        return $this;
    }
}
