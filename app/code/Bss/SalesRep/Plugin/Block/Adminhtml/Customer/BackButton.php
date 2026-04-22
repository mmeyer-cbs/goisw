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
namespace Bss\SalesRep\Plugin\Block\Adminhtml\Customer;

use Bss\SalesRep\Helper\Data;

/**
 * Class BackButton
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Customer
 */
class BackButton
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * BackButton constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Change Url Back Button if User is Sales Rep
     *
     * @param \Magento\Customer\Block\Adminhtml\Edit\BackButton $backButton
     * @param string $result
     * @return string
     */
    public function afterGetBackUrl(
        \Magento\Customer\Block\Adminhtml\Edit\BackButton $backButton,
        $result
    ) {
        $isSalesRep = $this->helper->checkUserIsSalesRep();
        if ($this->helper->isEnable() && $isSalesRep) {
            return $backButton->getUrl('salesrep/index/customer');
        }
        return $result;
    }
}
