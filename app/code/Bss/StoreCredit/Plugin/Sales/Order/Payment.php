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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Plugin\Sales\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Info;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;
use Magento\Sales\Api\CreditmemoManagementInterface as CreditmemoManager;
use Magento\Sales\Model\Order\Payment as OrderPayment;

/**
 * Class Payment
 * @package Bss\StoreCredit\Plugin\Sales\Order
 */
class Payment
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Plugin
     *
     * @param OrderPayment $subject
     */
    public function beforeRefund(OrderPayment $subject, $creditmemo)
    {
        $data = $this->request->getPost('creditmemo');
        $has_message = $subject->hasMessage();
        $baseAmountToRefund = $subject->formatAmount($creditmemo->getBaseGrandTotal());
        if (isset($data['storecredit']) && $data['storecredit'] == 1 && !$has_message) {
            $message = __('We refunded %1 to Store Credit.', $subject->formatPrice($baseAmountToRefund + $creditmemo->getBaseBssStorecreditAmount()));
            $subject->setMessage($message);
        }
    }
}
