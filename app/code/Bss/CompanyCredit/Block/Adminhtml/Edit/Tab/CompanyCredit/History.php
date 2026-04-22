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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit;

use Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History\Available;
use Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History\Change;
use Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History\Type;
use Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History\UnpaidCredit;
use Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History\UpdateCredit;
use Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History\ViewOrder;
use Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History\PaymentStatus;
use Bss\CompanyCredit\Model\HistoryFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class History extends Extended
{
    /**
     * @var \Bss\CompanyCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * Website collection
     *
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    private $websitesFactory;

    /**
     * Construct.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param HistoryFactory $historyFactory
     * @param CollectionFactory $websitesFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        HistoryFactory $historyFactory,
        CollectionFactory $websitesFactory,
        array $data = []
    ) {
        $this->historyFactory = $historyFactory;
        $this->websitesFactory = $websitesFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct.
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('bss_companycredit_tab_credit_history');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * Prepare Collection.
     *
     * @return $this
     */
    public function _prepareCollection()
    {
        try {
            $collection = $this->historyFactory->create()->getCollection()
                ->addFilterToMap('customer_id', 'main_table.customer_id')
                ->addFieldToFilter(
                    'customer_id',
                    $this->getRequest()->getParam('id')
                );
            $this->setCollection($collection);
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    public function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'id',
                'filter' => false,
            ]
        );
        $this->addColumn(
            'updated_time',
            [
                'header' => __('Updated At'),
                'type' => 'datetime',
                'align' => 'right',
                'index' => 'updated_time',
                'filter' => false,
            ]
        );
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'renderer' => Type::class,
                'filter' => false,
            ]
        );

        $this->addColumn(
            'change_credit',
            [
                'header' => __('Credit Change'),
                'align' => 'center',
                'index' => 'change_credit',
                'renderer' => Change::class,
                'filter' => false,
            ]
        );

        $this->addColumn(
            'available_credit_current',
            [
                'header' => __('Available Credit'),
                'align' => 'center',
                'index' => 'available_credit_current',
                'renderer' => Available::class,
                'filter' => false,
            ]
        );

        $this->addColumn(
            'comment',
            [
                'header' => __('Comment'),
                'align' => 'right',
                'index' => 'comment',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'po_number',
            [
                'header' => __('PO Number'),
                'align' => 'right',
                'index' => 'po_number',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'order_id',
            [
                'header' => __('Order ID'),
                'align' => 'right',
                'index' => 'order_id',
                'filter' => false,
                'sortable' => false,
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'align' => 'right',
                'index' => 'order_id',
                'sortable' => false,
                'filter' => false,
                'renderer' => ViewOrder::class
            ]
        );

        $this->addColumn(
            'payment_due_date',
            [
                'header' => __('Payment Due Date'),
                'type' => 'datetime',
                'align' => 'right',
                'index' => 'payment_due_date',
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            'payment_status',
            [
                'header' => __('Payment Status'),
                'index' => 'payment_status',
                'renderer' => PaymentStatus::class,
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            'unpaid_credit',
            [
                'header' => __('Unpaid Credit'),
                'align' => 'center',
                'index' => 'unpaid_credit',
                'renderer' => UnpaidCredit::class,
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            'update_credit',
            [
                'header' => __('Update Credit'),
                'align' => 'center',
                'renderer' => UpdateCredit::class,
                'sortable' => false,
                'filter' => false
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
        return $this->getUrl('companycredit/index/history', ['_current' => true]);
    }

    /**
     * Get row url
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return parent::getRowUrl(false);
    }
}
