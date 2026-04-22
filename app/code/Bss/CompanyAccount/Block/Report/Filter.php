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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Block\Report;

use Bss\CompanyAccount\Helper\Tabs as TabsOrder;
use Bss\CompanyAccount\Model\ResourceModel\SubUserOrder\CollectionFactory as Collection;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;

/**
 * Class Filter
 *
 * @package Bss\CompanyAccount\Block\Report
 */
class Filter extends Template
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var Collection
     */
    private $reportCollection;

    /**
     * @var Data
     */
    protected $dataPricing;

    /**
     * @var TabsOrder
     */
    protected $tabsHelper;

    /**
     * @var \Bss\CompanyAccount\Model\CompatibleQuoteExtension
     */
    protected $compatibleQuoteExtension;

    /**
     * Filter constructor.
     *
     * @param Template\Context $context
     * @param CurrentCustomer $currentCustomer
     * @param Collection $reportCollection
     * @param Data $dataPricing
     * @param TabsOrder $tabsHelper
     * @param \Bss\CompanyAccount\Model\CompatibleQuoteExtension $compatibleQuoteExtension
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CurrentCustomer  $currentCustomer,
        Collection       $reportCollection,
        Data             $dataPricing,
        TabsOrder        $tabsHelper,
        \Bss\CompanyAccount\Model\CompatibleQuoteExtension $compatibleQuoteExtension,
        array            $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->reportCollection = $reportCollection;
        $this->dataPricing = $dataPricing;
        $this->tabsHelper = $tabsHelper;
        $this->compatibleQuoteExtension = $compatibleQuoteExtension;
        parent::__construct($context, $data);
    }

    /**
     * Filter constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $collection = $this->reportCollection->create();
        $customerId = $this->currentCustomer->getCustomerId();
        if ($this->isFilterTable()) {
            $dateFrom = $this->getRequest()->getParam('datefrom');
            $dateTo = $this->getRequest()->getParam('dateto') . ' 23:59:59';
            $collection->getReportData()->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('main_table.created_at', ['gt' => $dateFrom])
                ->addFieldToFilter('main_table.created_at', ['lt' => $dateTo]);
            $this->setItems($collection);
        } else {
            $collection->getReportData()->addFieldToFilter('customer_id', $customerId);
            $this->setItems($collection);
        }
    }

    /**
     * Function check table has filter
     *
     * @return bool
     */
    public function isFilterTable()
    {
        if (!$this->getRequest()->getParam('datefrom') && !$this->getRequest()->getParam('dateto')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Format total value based on quote currency
     *
     * @param $total
     * @return float|string
     */
    public function formatValue($total)
    {
        return $this->dataPricing->currency((float)$total, true, false);
    }

    /**
     * Format date
     *
     * @param $time
     * @return string
     * @throws \Exception
     * @throws NoSuchEntityException
     */
    public function getFormatDate($time)
    {
        return $this->tabsHelper->getFormatDate($time);
    }

    /**
     * Get model Compatible QuoteExtension
     *
     * @return \Bss\CompanyAccount\Model\CompatibleQuoteExtension
     */
    public function getCompatibleQuoteExtension()
    {
        return $this->compatibleQuoteExtension;
    }
}
