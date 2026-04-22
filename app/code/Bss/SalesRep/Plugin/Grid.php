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
namespace Bss\SalesRep\Plugin;

use Bss\SalesRep\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;

/**
 * Class Grid
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Customer
 */
class Grid
{
    /**
     * Sale Rep id null
     */
    const NOT_SALES_REP = -1;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Grid constructor.
     * @param Session $authSession
     * @param Data $helper
     */
    public function __construct(
        Session $authSession,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->_authSession = $authSession;
    }

    /**
     * Filter grid customer of SalesRep
     *
     * @param CollectionFactory $subject
     * @param $collection
     * @param $requestName
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetReport(
        CollectionFactory $subject,
        $collection,
        $requestName
    ) {
        if ($requestName == 'salesrep_customer_listing_data_source') {
            $collection->addFilterToMap('sales_rep', 'admin_user.username');
            $collection->addFilterToMap('is_company_account', 'bss_is_company_account');
            $collection->addFilterToMap('email', 'main_table.email');
            $noSalesRep = self::NOT_SALES_REP;
            $salesRepId = $this->helper->getSalesRepId();
            array_push($salesRepId, $noSalesRep);
            $userId = $this->_authSession->getUser()->getId();
            if (in_array($userId, $salesRepId)) {
                $collection->addFieldToSelect('*')->addFieldToFilter('bss_sales_representative', $userId);
            } else {
                $collection->addFieldToSelect('*')->addFieldToFilter('bss_sales_representative', $salesRepId);
            }
            $collection->getSelect()->joinLeft(
                ['admin_user' => $collection->getTable('admin_user')],
                'main_table.bss_sales_representative = admin_user.user_id',
                ['sales_rep' => 'admin_user.username']
            );
        }
        return $collection;
    }
}
