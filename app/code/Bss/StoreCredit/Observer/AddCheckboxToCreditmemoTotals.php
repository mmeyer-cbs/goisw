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
namespace Bss\StoreCredit\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddCheckboxToCreditmemoTotals
 */
class AddCheckboxToCreditmemoTotals implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * AddCheckboxToCreditmemoTotals constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param Http $request
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Http $request
    ) {
        $this->orderRepository = $orderRepository;
        $this->request = $request;
    }

    /**
     * Add check box store credit
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->checkNewCreditMemo($observer)) {
            if ($this->checkOrderStoreCredit()) {
                $html = $this->htmlCheckBoxStoreCredit();
                $output = $observer->getTransport()->getOutput() . $html;
                $observer->getTransport()->setOutput($output);
            }
        }
    }

    /**
     * Check new credit memo to add check box store credit
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     */
    public function checkNewCreditMemo($observer)
    {
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        if (($action == 'updateQty' || $action == 'new') && $moduleName == 'sales' &&
            $controller == 'order_creditmemo' && $observer->getElementName() == 'submit_before') {
            return true;
        }
        return false;
    }

    /**
     * Check order has store credit
     *
     * @return bool
     */
    public function checkOrderStoreCredit()
    {
        $orderId = $this->request->getParam("order_id");
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order->getCustomerId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Html check box store credit
     *
     * @return string
     */
    public function htmlCheckBoxStoreCredit()
    {
        $html = '<div class="field choice admin__field admin__field-option field-storecredit-checkbox">
                        <input id="storecredit-refund"
                               class="admin__control-checkbox"
                               name="creditmemo[storecredit]"
                               value="1"
                               type="checkbox" />
                        <label for="storecredit-refund" class="admin__field-label">
                            <span>';
        $html .= __('Refund all to Store Credit');
        $html .= '</span>
                        </label>
                    </div>';
        return $html;
    }
}
