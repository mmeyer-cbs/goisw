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
namespace Bss\SalesRep\Plugin\Block\Adminhtml\Order;

use Bss\SalesRep\Helper\Data;

/**
 * Class CreateCustomer
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Order
 */
class CreateCustomer
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * CreateCustomer constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Remove create customer button in sales rep admin
     *
     * @param $subject
     * @param $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetButtonsHtml($subject, $result)
    {
        $isSaleRep = $this->helper->checkUserIsSalesRep();
        if ($this->helper->isEnable() && $isSaleRep) {
            return '';
        }
        return $result;
    }
}
