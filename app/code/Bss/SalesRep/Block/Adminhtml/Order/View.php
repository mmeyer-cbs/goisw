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
namespace Bss\SalesRep\Block\Adminhtml\Order;

use Bss\SalesRep\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\ConfigInterface;

/**
 * Class View
 *
 * @package Bss\SalesRep\Block\Adminhtml\Order
 */
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * View constructor.
     * @param Data $helper
     * @param Context $context
     * @param Registry $registry
     * @param ConfigInterface $salesConfig
     * @param Reorder $reorderHelper
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Context $context,
        Registry $registry,
        ConfigInterface $salesConfig,
        Reorder $reorderHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }

    /**
     * Changer return true
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            return true;
        }
        return parent::_isAllowedAction($resourceId);
    }
}
