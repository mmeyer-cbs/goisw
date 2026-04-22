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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Block\Adminhtml\PriceRule\Edit\Tab\GeneralInformation\CustomerConditions;

use Bss\CustomPricing\Model\Config\Source\CustomerGroups;
use Magento\Backend\Block\Widget\Grid\Column;
use Bss\CustomPricing\Helper\Data;

/**
 * Class SpecifiedGrid for specified cond option
 *
 * @method setId(string $id)
 * @method getJsFormObject()
 * @method setCheckboxCheckCallback(string $callback)
 * @method setRowInitCallback(string $initCallback)
 * @method setUseAjax($value)
 */
class SpecifiedGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $customerCollectionInstance;

    /**
     * @var CustomerGroups
     */
    protected $customerGroupsSource;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helper;

    /**
     * SpecifiedGrid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFac
     * @param CustomerGroups $customerGroupsSource
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFac,
        CustomerGroups $customerGroupsSource,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->customerCollectionFactory = $customerCollectionFac;
        $this->customerGroupsSource = $customerGroupsSource;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('specifiedChooserGrid_' . $this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setDefaultSort('sku');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Custom filter result collection
     *
     * @param Column $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in customer flag
        // Khi search trên form thì chỗ này để set mấy cái điều kiện custom các thứ

        if ($column->getId() == 'in_customers') {
            $selected = $this->getSelectedCustomers();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $selected]);
            }
        } elseif ($column->getIndex() === 'customer_name') {
            // make fulltext search
            $requestCName = preg_replace('/([\s])\1+/', ' ', $column->getFilter()->getValue());

            $this->getCollection()->getSelect()->where(
                new \Zend_Db_Expr(sprintf(
                    "CONCAT(COALESCE(`e`.`firstname`,''), ' ', COALESCE(`e`.`middlename`,''), ' ', COALESCE(`e`.`lastname`,'')) LIKE '%s%%'",
                    $requestCName
                ))
            )->orWhere(
                new \Zend_Db_Expr(sprintf(
                    "CONCAT(COALESCE(`e`.`firstname`,''), ' ', COALESCE(`e`.`lastname`,''), ' ', COALESCE(`e`.`middlename`,'')) LIKE '%s%%'",
                    $requestCName
                ))
            )->orWhere(
                new \Zend_Db_Expr(sprintf(
                    "CONCAT(COALESCE(`e`.`middlename`,''), ' ', COALESCE(`e`.`firstname`,''), ' ', COALESCE(`e`.`lastname`,'')) LIKE '%s%%'",
                    $requestCName
                ))
            )->orWhere(
                new \Zend_Db_Expr(sprintf(
                    "CONCAT(COALESCE(`e`.`middlename`,''), ' ', COALESCE(`e`.`lastname`,''), ' ', COALESCE(`e`.`firstname`,'')) LIKE '%s%%'",
                    $requestCName
                ))
            )->orWhere(
                new \Zend_Db_Expr(sprintf(
                    "CONCAT(COALESCE(`e`.`lastname`,''), ' ', COALESCE(`e`.`firstname`,''), ' ', COALESCE(`e`.`middlename`,'')) LIKE '%s%%'",
                    $requestCName
                ))
            )->orWhere(
                new \Zend_Db_Expr(sprintf(
                    "CONCAT(COALESCE(`e`.`lastname`,''), ' ', COALESCE(`e`.`middlename`,''), ' ', COALESCE(`e`.`firstname`,'')) LIKE '%s%%'",
                    $requestCName
                ))
            );
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        $customerPerWebsite = $this->helper->isScopeCustomerPerWebsite();
        // luôn search theo cả website id đã chọn
        if ($customerPerWebsite && ($websiteId = $this->getWebsiteId())) {
            $this->getCollection()->addFieldToFilter('website_id', ['eq' => $websiteId]);
        }
        return $this;
    }

    /**
     * Prepare Customer data in specified Customer Conditions chooser
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getCustomerCollectionInstance();
        $customerPerWebsite = $this->helper->isScopeCustomerPerWebsite();
        if ($customerPerWebsite && ($websiteId = $this->getWebsiteId())) {
            $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Define Chooser Grid Columns and filters
     *
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_customers',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_customers',
                'values' => $this->getSelectedCustomers(),
                'align' => 'center',
                'index' => 'entity_id',
                'use_index' => true
            ]
        );

        $this->addColumn(
            'entity_id',
            ['header' => __('ID'), 'sortable' => true, 'width' => "30px", 'index' => 'entity_id']
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Customer Name'),
                'width' => '75px',
                'index' => "customer_name",
                "renderer" => CustomerNameColRenderer::class
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Customer Email'),
                'width' => '100px',
                'index' => 'email'
            ]
        );

        $groups = $this->customerGroupsSource->getHashOptionArray();

        $this->addColumn(
            'group_id',
            [
                'header' => __('Customer Groups'),
                'width' => '100px',
                'index' => 'group_id',
                'type' => 'options',
                'options' => $groups
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get customer resource collection instance
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function getCustomerCollectionInstance()
    {
        if (!$this->customerCollectionInstance) {
            $this->customerCollectionInstance = $this->customerCollectionFactory->create();
        }
        return $this->customerCollectionInstance;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/chooser',
            ['_current' => true, 'current_grid_id' => $this->getId(), 'collapse' => null]
        );
    }

    /**
     * Get selected customer in condition grid
     *
     * @return array
     */
    protected function getSelectedCustomers()
    {
        return $this->getRequest()->getPost('selected', []);
    }

    /**
     * Get website id param
     *
     * @return int|null
     */
    protected function getWebsiteId()
    {
        return $this->getRequest()->getParam('website_id');
    }
}
