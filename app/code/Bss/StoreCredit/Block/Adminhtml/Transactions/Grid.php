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
namespace Bss\StoreCredit\Block\Adminhtml\Transactions;

use Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Email;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Bss\StoreCredit\Model\HistoryFactory;
use Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Customer;
use Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Balance;
use Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Change;
use Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Addition;
use Bss\StoreCredit\Model\ResourceModel\History\Grid\CollectionFactory as HistoryCollectionFactory;

class Grid extends Extended
{
    /**
     * @var \Bss\StoreCredit\Model\ResourceModel\History\CollectionFactory
     */
    private $historyFactory;

    /**
     * @var \Bss\StoreCredit\Model\ResourceModel\History\Grid\CollectionFactory
     */
    private $historyCollection;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param HistoryFactory $historyFactory
     * @param HistoryCollectionFactory $historyCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        HistoryFactory $historyFactory,
        HistoryCollectionFactory $historyCollection,
        array $data = []
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyCollection = $historyCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('gridStoreCredit');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    public function _prepareCollection()
    {
        /** @var $collection \Bss\StoreCredit\Model\ResourceModel\History\Collection */
        $collection = $this->historyCollection->create();
        if ($websiteId = $this->getRequest()->getParam('website')) {
            $collection->prepareForStoreCreditTransactions($websiteId);
        }
        $collection->getSelect()->joinLeft(
            ['customer_grid_flat' => $collection->getTable('customer_grid_flat')],
            'main_table.customer_id = customer_grid_flat.entity_id',
            [
                "IF(customer_grid_flat.`entity_id`, `customer_grid_flat`.`name`, main_table.`customer_name`) as customer_name_flat",
                "IF(customer_grid_flat.`entity_id`, `customer_grid_flat`.`email`,main_table.`customer_email`) as customer_email_flat",
                "customer_grid_flat.entity_id"
            ]
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Exception
     */
    public function _prepareColumns()
    {
        $this->addColumn(
            'history_id',
            [
                'header' => __('Transaction ID'),
                'index' => 'history_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Action'),
                'index' => 'type'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Customer'),
                'index' => 'customer_grid_flat.name',
                'type' => 'text',
                'renderer' => Customer::class
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'customer_grid_flat.email',
                'type' => 'text',
                'renderer' => Email::class
            ]
        );

        $this->addColumn(
            'change_amount',
            [
                'header' => __('Balance Change'),
                'index' => 'change_amount',
                'renderer' => Change::class
            ]
        );

        $this->addColumn(
            'balance_amount',
            [
                'header' => __('Balance'),
                'index' => 'balance_amount',
                'renderer' => Balance::class
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Action'),
                'index' => 'type',
                'type' => 'options',
                'options' => $this->historyFactory->create()->toOptionHash()
            ]
        );

        $this->addColumn(
            'updated_time',
            [
                'header' => __('Date'),
                'index' => 'updated_time',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'comment_content',
            [
                'header' => __('Additional Info'),
                'index' => 'comment_content',
                'sortable' => false,
                'filter' => false,
                'renderer' => Addition::class
            ]
        );

        return parent::_prepareColumns();
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
