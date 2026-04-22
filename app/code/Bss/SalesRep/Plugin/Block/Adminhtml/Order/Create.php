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
 * Class Create
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Order
 */
class Create
{
    /**
     * Bss Helper
     *
     * @var Data
     */
    protected $helper;

    /**
     * Create constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get back button Url
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create $subject
     * @param string $result
     * @return string
     */
    public function afterGetBackUrl($subject, $result)
    {
        $isSaleRep = $this->helper->checkUserIsSalesRep();
        if ($this->helper->isEnable() && $isSaleRep) {
            return $subject->getUrl('salesrep/index/order');
        }
        return $result;
    }
}
