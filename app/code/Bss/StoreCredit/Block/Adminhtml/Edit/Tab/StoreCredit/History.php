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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Block\Adminhtml\Edit\Tab\StoreCredit;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Bss\StoreCredit\Model\HistoryFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Balance;
use Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Change;
use Bss\StoreCredit\Block\Adminhtml\Edit\Tab\StoreCredit\History\Addition;
use Magento\Framework\App\ResourceConnection;

/**
 * Class History
 */
class History extends Extended
{
    /**
     * @var \Bss\StoreCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * Website collection
     *
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    private $websitesFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param HistoryFactory $historyFactory
     * @param CollectionFactory $websitesFactory
     * @param ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        HistoryFactory $historyFactory,
        CollectionFactory $websitesFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->historyFactory = $historyFactory;
        $this->websitesFactory = $websitesFactory;
        $this->resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('bss_storecredit_tab_credit_history');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    public function _prepareCollection()
    {
        $collection = $this->historyFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(
            [
                'sales_order_table' => $this->resource->getConnection()->getTableName('sales_order')
            ],
            'main_table.order_id = sales_order_table.entity_id',
            [
                'order_increment_id' => 'sales_order_table.increment_id'
            ]
        )->joinLeft(
            [
                'creditmemo_table' => $this->resource->getConnection()->getTableName('sales_creditmemo')
            ],
            'main_table.creditmemo_id = creditmemo_table.entity_id',
            [
                'creditmemo_increment_id' => 'creditmemo_table.increment_id'
            ]
        );
        $collection->addFieldToFilter(
            'main_table.customer_id',
            $this->getRequest()->getParam('id')
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function _prepareColumns()
    {
        $this->addColumn(
            'history_id',
            [
                'header' => __('Transaction ID'),
                'index' => 'history_id'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'type' => 'options',
                'options' => $this->historyFactory->create()->toOptionHash()
            ]
        );

        $this->addColumn(
            'change_amount',
            [
                'header' => __('Change'),
                'align' => 'right',
                'type' => 'currency',
                'index' => 'change_amount',
                'renderer' => Change::class
            ]
        );

        $this->addColumn(
            'balance_amount',
            [
                'header' => __('Balance'),
                'align' => 'right',
                'type' => 'currency',
                'index' => 'balance_amount',
                'renderer' => Balance::class
            ]
        );

        $this->addColumn(
            'is_notified',
            [
                'header' => __('Is Notified'),
                'align' => 'right',
                'type' => 'options',
                'options' => [0 => __('No'), 1 => __('Yes')],
                'index' => 'is_notified'
            ]
        );

        $this->addColumn(
            'website_id',
            [
                'header' => __('Website'),
                'align' => 'right',
                'type' => 'options',
                'index' => 'website_id',
                'options' => $this->websitesFactory->create()->toOptionHash()
            ]
        );

        $this->addColumn(
            'updated_time',
            [
                'header' => __('Updated At'),
                'type' => 'datetime',
                'align' => 'right',
                'index' => 'updated_time'
            ]
        );

        $this->addColumn(
            'addition_info',
            [
                'header' => __('Additional Info'),
                'align' => 'right',
                'index' => 'comment_content',
                'sortable' => false,
                'filter' => false,
                'renderer' => Addition::class
            ]
        );

        $this->setEmptyText(__('There are no items.'));
        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid reload url
     *
     * @return string;
     */
    public function getGridUrl()
    {
        return $this->getUrl('storecredit/index/history', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return parent::getRowUrl(false);
    }
}
