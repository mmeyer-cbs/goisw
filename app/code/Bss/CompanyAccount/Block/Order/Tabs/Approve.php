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

namespace Bss\CompanyAccount\Block\Order\Tabs;

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\Tabs as TabsOrder;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\OrderFactory;

/**
 * Tabs Approved Order History
 *
 * @api
 * @since 100.0.2
 */
class Approve extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TabsOrder
     */
    protected $tabsHelper;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var Reorder
     */
    protected $reorderHelper;

    /**
     * @var PostHelper
     */
    protected $postDataHelper;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Function construct
     *
     * @param OrderFactory $orderFactory
     * @param Context $context
     * @param Session $customerSession
     * @param Data $helper
     * @param TabsOrder $tabsHelper
     * @param UrlInterface $urlInterface
     * @param QuoteFactory $quoteFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param HttpContext $httpContext
     * @param Reorder $reorderHelper
     * @param PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        OrderFactory $orderFactory,
        Template\Context         $context,
        Session                  $customerSession,
        Data                     $helper,
        TabsOrder                $tabsHelper,
        UrlInterface             $urlInterface,
        QuoteFactory             $quoteFactory,
        OrderRepositoryInterface $orderRepository,
        HttpContext              $httpContext,
        Reorder                  $reorderHelper,
        PostHelper               $postDataHelper,
        array                    $data = []
    ) {
        $this->orderFactory=$orderFactory;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->tabsHelper = $tabsHelper;
        $this->urlInterface = $urlInterface;
        $this->quoteFactory = $quoteFactory;
        $this->orderRepository = $orderRepository;
        $this->httpContext = $httpContext;
        $this->reorderHelper = $reorderHelper;
        $this->postDataHelper = $postDataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get customer account URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Get customer quotes
     *
     * @return \Bss\CompanyAccount\Model\ResourceModel\SubUserQuote\Collection|false
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuotes()
    {
        return $this->tabsHelper->getQuotes('approved');
    }

    /**
     * Pagination
     *
     * @return $this|Waiting
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotes()) {
            $pager = $this->getLayout()
                ->createBlock(\Magento\Theme\Block\Html\Pager::class, 'bss.sales.order.history.approve.pager')
                ->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])
                ->setShowPerPage(true)
                ->setCollection($this->getQuotes());
            $this->setChild('pager', $pager);
            $this->getQuotes()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Function format currency
     *
     * @param $price
     * @return float|string
     */
    public function formatCurrency($price)
    {
        return $this->tabsHelper->formatCurrency($price);
    }

    /**
     * Get message for no quotes.
     *
     * @return \Magento\Framework\Phrase
     * @since 102.1.0
     */
    public function getEmptyQuotesMessage()
    {
        return $this->tabsHelper->getEmptyQuotesMessage();
    }

    /**
     * Define who created/approved/rejected order
     *
     * @param $subId
     * @return mixed|string
     * @throws LocalizedException
     */
    public function actionBy($subId)
    {
        if ($subId == 0) {
            return $this->customerSession->getCustomer()->getName();
        } else {
            return $this->tabsHelper->actionBy($subId);
        }
    }

    /**
     * Allow sub user approve order
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function approveOrder()
    {
        return $this->tabsHelper->approveOrder();
    }

    /**
     * Function check sendOrderWaiting
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendOrderWaiting()
    {
        return $this->tabsHelper->sendOrderWaiting();
    }

    /**
     * Check if customer is sub user
     *
     * @return bool
     */
    public function isCompanyAccount()
    {
        return $this->helper->isCompanyAccount();
    }

    /**
     * Display status of approve tab
     *
     * @param $id
     * @return string
     * @throws NoSuchEntityException
     */
    public function getApproveStatus($id)
    {
        return $this->tabsHelper->getApproveStatus($id);
    }

    /**
     * Function get view url
     *
     * @param $subUserQuote
     * @return string
     */
    public function getViewUrl($subUserQuote)
    {
        $quoteId=$subUserQuote->getQuoteId();
        $order=$this->getOrder($quoteId);
        if ($order->getId()) {
            return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('companyaccount/order/view', ['order_id' => $quoteId]);
    }

    /**
     * Set sort order for tabs
     *
     * @return string
     */
    public function setSortOrder()
    {
        return $this->tabsHelper->setSortOrder();
    }

    /**
     * Identify sorting in-use
     *
     * @return string
     */
    public function getSortIconId()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'entity_id'))
            ? $this->tabsHelper->getSortIcon() : '';
    }

    /**
     * Identify sorting in-use
     *
     * @return string
     */
    public function getSortIconDate()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'created_at'))
            ? $this->tabsHelper->getSortIcon() : '';
    }

    /**
     * Identify sorting in-use
     *
     * @return string
     */
    public function getSortIconCreatedBy()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'sub_id'))
            ? $this->tabsHelper->getSortIcon() : '';
    }

    /**
     * Identify sorting in-use
     *
     * @return string
     */
    public function getSortIconEstimateTotal()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'subtotal'))
            ? $this->tabsHelper->getSortIcon() : '';
    }

    /**
     * Identify sorting in-use
     *
     * @return string
     */
    public function getSortIconStatus()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'reserved_order_id'))
            ? $this->tabsHelper->getSortIcon() : '';
    }

    /**
     * Identify sorting in-use
     *
     * @return string
     */
    public function getSortIconApprovedBy()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'action_by'))
            ? $this->tabsHelper->getSortIcon() : '';
    }

    /**
     * Function check role place order
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canPlaceOrder()
    {
        return $this->tabsHelper->isPlaceOrder();
    }

    /**
     * Function get checkout url
     *
     * @param $order
     * @return string
     */
    public function getCheckOutUrl($order)
    {
        return $this->getUrl('companyaccount/order/checkout', ['order_id' => $order->getQuoteId()]);
    }

    /**
     * Function get reserved order id in quote object
     *
     * @param $quoteId
     * @return false|mixed|string
     */
    public function getReservedOrderId($quoteId)
    {
        return $this->quoteFactory->create()->load($quoteId)->getReservedOrderId();
    }

    /**
     * Function get order
     *
     * @param $quoteId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder($quoteId)
    {
        $reserverOrderId=$this->getReservedOrderId($quoteId);
        return $this->orderFactory->create()->loadByIncrementId($reserverOrderId);
    }

    /**
     * Get url for reorder action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        if (!$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
            return $this->getUrl('sales/guest/reorder', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * Function check canCheckOut
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canCheckout()
    {
        if ($this->canPlaceOrder() || $this->sendOrderWaiting()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function check can reorder
     *
     * @param $id
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function checkReorder($id)
    {
        $reserverOrderId=$this->getReservedOrderId($id);
        $order=$this->orderFactory->create()->loadByIncrementId($reserverOrderId);
        if ($this->reorderHelper->canReorder($order->getId())) {
            return $this->getOrder($id);
        } else {
            return false;
        }
    }

    /**
     * Function get post data reorder
     *
     * @param $order
     * @return string
     */
    public function getPostDataReorder($order)
    {
        return $this->postDataHelper->getPostData($this->getReorderUrl($order));
    }

    /**
     * Check if sub-user can view tabs
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isViewTabs()
    {
        if ($subUser = $this->customerSession->getSubUser()) {
            $roleId = $subUser->getRoleId();
            return $this->tabsHelper->canViewTabs((int)$roleId);
        }
        return true;
    }

    /**
     * Return tab url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('sales/order/history', ['tab' => 'approve']);
    }

    /**
     * Get sort order url
     *
     * @param string $field
     * @return string
     */
    public function getSortOrderUrl($field)
    {
        return $this->getUrl(
            'sales/order/history',
            ['_query' => [
                'field' => $field,
                'sort' => $this->setSortOrder()],
                'tab' => 'approve'
            ]
        );
    }

    /**
     * @return bool
     */
    public function isTabActive()
    {
        $name = $this->getRequest()->getParam('tab');
        return $name == 'approve';
    }
}
