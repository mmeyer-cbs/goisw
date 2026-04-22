<?php
/**
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

namespace Bss\StoreCredit\Block\Sales\Order\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Bss\StoreCredit\Model\Credit;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Block\Order\Totals as OrderTotals;
use Magento\Sales\Block\Order\Creditmemo\Totals as CreditmemoTotals;

/**
 * Class Totals
 * @package Bss\StoreCredit\Block\Sales\Order
 */
class Totals extends Template
{
    /**
     * @var \Bss\StoreCredit\Model\Credit
     */
    private $creditModel;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param Context $context
     * @param Credit $creditModel
     * @param OrderRepositoryInterface $orderRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Credit $creditModel,
        OrderRepositoryInterface $orderRepository,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditModel = $creditModel;
        $this->orderRepository = $orderRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $source = $this->getParentBlock()->getSource();
        $orderId = (int) $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            return $this;
        }
        $order = $this->orderRepository->get($orderId);
        if ($source->getBaseBssStorecreditAmount()) {
            $total = $this->dataObjectFactory->create();
            $total->setCode('store_credit')
                ->setValue(-$source->getBssStorecreditAmount())
                ->setLabel(__('Store Credit'));
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        $type = $this->getParentBlock()->getType();

        $creditmemoBaseBssStorecreditAmountRefund =  '';
        if ($type == OrderTotals::class) {
            $creditmemoBaseBssStorecreditAmountRefund = $this->creditModel->getCreditRefundBase($order);
        }
        if ($type == CreditmemoTotals::class) {
            $creditmemoBaseBssStorecreditAmountRefund = $source->getBssStorecreditAmountRefund();
        }
        if ($creditmemoBaseBssStorecreditAmountRefund) {
            $refundStoreCredit = $this->dataObjectFactory->create();
            $refundStoreCredit->setCode('refund_store_credit')
                ->setStrong(true)
                ->setValue($creditmemoBaseBssStorecreditAmountRefund)
                ->setLabel(__('Refund to Store Credit'))
                ->setArea('footer');
            $this->getParentBlock()->addTotalBefore($refundStoreCredit, 'grand_total');
        }
        return $this;
    }
}
