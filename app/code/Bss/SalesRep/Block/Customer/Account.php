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
namespace Bss\SalesRep\Block\Customer;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\ResourceModel\SalesRep;
use Exception;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Zend_Db_Statement_Exception;

/**
 * Class Account
 *
 * @package Bss\SalesRep\Block\Customer
 */
class Account extends Template
{
    /**
     * @var SalesRep
     */
    protected $salesRep;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Account constructor.
     *
     * @param Data $helper
     * @param FilterProvider $filterProvider
     * @param SalesRep $salesRep
     * @param Customer $customer
     * @param Session $customerSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        FilterProvider $filterProvider,
        SalesRep $salesRep,
        Customer $customer,
        Session $customerSession,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        $this->salesRep = $salesRep;
        $this->customer = $customer;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Get sales rep by customer id
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function getSalesRep()
    {
        $customerId = $this->customerSession->getId();
        $customer = $this->customer->load($customerId)->getData();
        if (!empty($customer['bss_sales_representative'])) {
            return $this->salesRep->joinTableUser($customer['bss_sales_representative']);
        }
        return [];
    }

    /**
     * Name Sales Rep
     *
     * @param array $salesRep
     * @return mixed|string
     */
    public function getName($salesRep)
    {
        if ($salesRep != null) {
            return $salesRep[0]['name'];
        }
        return '';
    }

    /**
     * Information Sales Rep
     *
     * @param array $salesRep
     * @return mixed|string
     * @throws Exception
     */
    public function getInformation($salesRep)
    {
        if ($salesRep != null && $salesRep[0]['information'] != null) {
            return $this->filterProvider->getBlockFilter()->filter($salesRep[0]['information']);
        }
        return '';
    }

    /**
     * Check module enable
     *
     * @return bool
     */
    public function isEnable()
    {
        if ($this->helper->isEnable()) {
            return true;
        }
        return false;
    }
}
