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
namespace Bss\SalesRep\Plugin\Order\View\Tab;

use Bss\SalesRep\Helper\Data;

class InvoicesSalesRep
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Check sales rep and show invoices tab
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Tab\Invoices $subject
     * @param bool $result
     * @return bool|mixed
     */
    public function afterCanShowTab($subject, $result)
    {
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            $result = true;
        }
        return $result;
    }
}
