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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Block\Adminhtml\Order\Totals;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Bss\StoreCredit\Model\Credit;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Block\Adminhtml\Order\Totals;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals as CreditmemoTotals;

/**
 * Class StoreCredit
 */
class StoreCredit extends Template
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
     * @param Context $context
     * @param Credit $creditModel
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Credit $creditModel,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditModel = $creditModel;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Add store credit info to total
     *
     * @return $this
     */
    public function initTotals()
    {
        $source = $this->getParentBlock()->getSource();
        if ($source->getBaseBssStorecreditAmount()) {
            $total = $this->dataObjectFactory->create();
            $total->setCode('store_credit')
                ->setValue(-$source->getBssStorecreditAmount())
                ->setBaseValue(-$source->getBaseBssStorecreditAmount())
                ->setLabel(__('Store Credit'));
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        $type = $this->getParentBlock()->getType();

        $creditmemoBaseBssStorecreditAmountRefund =  '';
        $creditmemoBssStorecreditAmountRefund =  '';
        if ($type == Totals::class) {
            $creditmemoBaseBssStorecreditAmountRefund = $this->creditModel->getCreditRefundBase($this->getOrder());
            $creditmemoBssStorecreditAmountRefund = $this->creditModel->getCreditRefund($this->getOrder());
        }
        if ($type == CreditmemoTotals::class) {
            $creditmemoBaseBssStorecreditAmountRefund = $source->getBaseBssStorecreditAmountRefund();
            $creditmemoBssStorecreditAmountRefund = $source->getBssStorecreditAmountRefund();
        }
        if ($creditmemoBaseBssStorecreditAmountRefund) {
            $refundStoreCredit = $this->dataObjectFactory->create();
            $refundStoreCredit->setCode('refund_store_credit')
                ->setStrong(true)
                ->setValue($creditmemoBssStorecreditAmountRefund)
                ->setBaseValue($creditmemoBaseBssStorecreditAmountRefund)
                ->setLabel(__('Refund to Store Credit'))
                ->setArea('footer');
            $this->getParentBlock()->addTotalBefore($refundStoreCredit, 'grand_total');
        }
        return $this;
    }
}
