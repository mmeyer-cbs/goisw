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

namespace Bss\CompanyAccount\Block\Order;

use Bss\CompanyAccount\Helper\Tabs as TabsOrder;
use Bss\CompanyAccount\Model\SubUserQuoteRepository;
use Magento\Customer\Model\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Block Buttons links in Order view page
 */
class Buttons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Bss_CompanyAccount::order/buttons.phtml';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var SubUserQuoteRepository
     */
    protected $subUserQuote;

    /**
     * @var TabsOrder
     */
    protected $tabsHelper;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Function construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param SubUserQuoteRepository $subUserQuote
     * @param TabsOrder $tabsHelper
     * @param QuoteFactory $quoteFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        SubUserQuoteRepository $subUserQuote,
        TabsOrder $tabsHelper,
        QuoteFactory $quoteFactory,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->subUserQuote = $subUserQuote;
        $this->tabsHelper = $tabsHelper;
        $this->quoteFactory = $quoteFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->quoteFactory->create()->load($this->getRequest()->getParam('order_id'));
    }

    /**
     * Function get Order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        $id = $this->getQuote()->getReservedOrderId();
        return $this->orderRepository->get((int)$id);
    }
    /**
     * Get url for printing order
     *
     * @param string $order
     * @return string
     */
    public function getPrintUrl($order)
    {
        return $this->getUrl('companyaccount/order/print', ['order_id' => $order]);
    }

    /**
     * Function check can re-order
     *
     * @return false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkReorder()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        $quoteStatus = $this->subUserQuote->getByQuoteId((int)$quoteId)->getQuoteStatus();
        if ($quoteStatus === 'approved') {
            if ($this->tabsHelper->getApproveStatus($quoteId) === 'Ordered') {
                return true;
            }
        }
        return false;
    }

    /**
     * Get url for reorder action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('sales/guest/reorder', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }
}
