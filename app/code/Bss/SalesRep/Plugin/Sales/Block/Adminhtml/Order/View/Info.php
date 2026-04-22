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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Plugin\Sales\Block\Adminhtml\Order\View;

use Bss\SalesRep\Helper\Data;

class Info
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
     * Check sales rep and can comment
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View\History $subject
     * @param \Magento\Sales\Model\Order\Address $address
     * @param string $label
     * @return bool|mixed
     */
    public function beforeGetAddressEditLink($subject, $address, $label = "")
    {
        if ($subject->getOrder()->canComment() && $this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            $this->helper->setIsAllowed("Magento_Sales::actions_edit");
        }
        return [$address, $label];
    }
}

