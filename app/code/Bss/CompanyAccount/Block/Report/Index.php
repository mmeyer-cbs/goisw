<?php
declare(strict_types = 1);

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
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;

/**
 * Class Index
 *
 * @package Bss\CompanyAccount\Block\Report
 */
class Index extends \Magento\Framework\View\Element\Template
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
     * Index constructor.
     *
     * @param Template\Context $context
     * @param CurrentCustomer $currentCustomer
     * @param Collection $reportCollection
     * @param Data $dataPricing
     * @param TabsOrder $tabsHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CurrentCustomer  $currentCustomer,
        Collection       $reportCollection,
        Data             $dataPricing,
        TabsOrder        $tabsHelper,
        array            $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->reportCollection = $reportCollection;
        $this->dataPricing = $dataPricing;
        $this->tabsHelper = $tabsHelper;
        parent::__construct($context, $data);
    }

    /**
     * Report Index constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $customerId = $this->currentCustomer->getCustomerId();
        $collection = $this->reportCollection->create();
        $collection->getReportData()->addFieldToFilter('customer_id', $customerId);
        $this->setItems($collection);
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
     * @throws Exception
     */
    public function getFormatDate($time)
    {
        return $this->tabsHelper->getFormatDate($time);
    }
}
