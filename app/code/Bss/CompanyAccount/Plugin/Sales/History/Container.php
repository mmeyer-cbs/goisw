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
namespace Bss\CompanyAccount\Plugin\Sales\History;

use Magento\Framework\Registry;

/**
 * Class Container
 *
 * @package Bss\CompanyAccount\Plugin\Sales\History
 */
class Container
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Container constructor.
     *
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {

        $this->registry = $registry;
    }

    /**
     * Register order
     *
     * @param \Magento\Sales\Block\Order\History\Container $subject
     * @param callable $proceed
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetOrder(
        \Magento\Sales\Block\Order\History\Container $subject,
        callable $proceed,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->registry->unregister('bss_reg_history_order');
        $this->registry->register('bss_reg_history_order', $order);
        return $proceed($order);
    }
}
