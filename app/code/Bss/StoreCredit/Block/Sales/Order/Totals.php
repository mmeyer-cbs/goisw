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

namespace Bss\StoreCredit\Block\Sales\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Bss\StoreCredit\Model\Credit;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Block\Order\Totals as OrderTotals;
use Magento\Sales\Block\Order\Creditmemo\Totals as CreditmemoTotals;

/**
 * Class Totals
 * @package Bss\StoreCredit\Block\Sales\Order
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var \Bss\StoreCredit\Model\Credit
     */
    private $creditModel;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Totals constructor.
     * @param DataObjectFactory $dataObjectFactory
     * @param Credit $creditModel
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        Credit $creditModel,
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->creditModel = $creditModel;
        parent::__construct($context, $registry, $data);
    }
    /**
     * @return $this
     */
    public function initTotals()
    {
        $source = $this->getParentBlock()->getSource();
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
            $creditmemoBaseBssStorecreditAmountRefund = $this->creditModel->getCreditRefundBase($this->getOrder());
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
