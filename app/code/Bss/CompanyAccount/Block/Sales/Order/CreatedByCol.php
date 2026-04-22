<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Block\Sales\Order;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Block\Sales\SubUserInfoHelper;
use Magento\Framework\View\Element\Template;

/**
 * Class CreatedByCol
 *
 * @package Bss\CompanyAccount\Block\Sales\Order
 */
class CreatedByCol extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $reg;

    /**
     * @var SubUserInfoHelper
     */
    private $subUserInfoHelper;

    /**
     * CreatedByCol constructor.
     *
     * @param \Magento\Framework\Registry $reg
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $reg,
        SubUserInfoHelper           $subUserInfoHelper,
        Template\Context            $context,
        array                       $data = []
    ) {
        $this->reg = $reg;
        $this->subUserInfoHelper = $subUserInfoHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get created sub-user name
     *
     * @return string
     */
    public function getCreatedBy()
    {
        $order = $this->getOrder();
        if ($order) {
            $subUserInfo = $this->subUserInfoHelper->getSubUserInfo($order->getEntityId());
            if ($subUserInfo) {
                return $subUserInfo[SubUserInterface::NAME];
            }
            return $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        }
        return '';
    }

    /**
     * Get shipping recipient
     *
     * @return mixed|string
     */
    public function getShipTo()
    {
        $order = $this->getOrder()->getShippingAddress();
        if ($order) {
                $firstName = $order->getFirstname();
                $lastName = $order->getLastname();
                return $firstName . ' ' . $lastName;
        } else {
            return '';
        }
    }

    /**
     * Get registered order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrder()
    {
        return $this->reg->registry('bss_reg_history_order');
    }
}
