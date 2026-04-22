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
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Class View
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Order
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class View
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * View constructor.
     * @param Data $helper
     * @param Registry $coreRegistry
     */
    public function __construct(
        Data $helper,
        Registry $coreRegistry
    ) {
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Remove back button in order view
     *
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        if ($this->helper->isEnable()) {
            if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
                return [$context, $buttonList];
            }
            $isSalesRep = $this->helper->checkUserIsSalesRep();

            if ($isSalesRep) {
                $buttonList->remove('back');
            }

            return [$context, $buttonList];
        }
        return [$context, $buttonList];
    }
}
