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
namespace Bss\SalesRep\Block\Adminhtml\Sales\Order\View;

use Bss\SalesRep\Model\ResourceModel\SalesRepOrder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\User\Model\ResourceModel\User\Collection;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Block\Adminhtml\Order\View
 */
class SalesRep extends Template
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var SalesRepOrder
     */
    protected $order;

    /**
     * SalesRep constructor.
     * @param Context $context
     * @param Collection $collection
     * @param SalesRepOrder $order
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Collection $collection,
        SalesRepOrder $order,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collection = $collection;
        $this->order = $order;
    }

    /**
     * Get name Sales Rep
     *
     * @return mixed|string
     */
    public function getNameSalesRep()
    {
        $userName = '';
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->order->joinTableOrder();
        foreach ($order as $item) {
            if ($item['order_id'] == $orderId) {
                return $item['user_name'];
            }
        }
        return $userName;
    }
}
