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
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Helper\Tabs as TabsOrder;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Model\ResourceModel\SubUserQuote\Collection;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Tabs Waiting Order History
 *
 * @api
 * @since 100.0.2
 */
class Waiting extends Template
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
     * @var PermissionsChecker
     */
    protected $permissionChecker;

    /**
     * @var CheckoutSession
     */
    private $checoutSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Data $helper
     * @param TabsOrder $tabsHelper
     * @param UrlInterface $urlInterface
     * @param PermissionsChecker $permissionChecker
     * @param CheckoutSession $checoutSession
     * @param array $data
     */
    public function __construct(
        Template\Context   $context,
        Session            $customerSession,
        Data               $helper,
        TabsOrder          $tabsHelper,
        UrlInterface       $urlInterface,
        PermissionsChecker $permissionChecker,
        CheckoutSession    $checoutSession,
        array              $data = []
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->tabsHelper = $tabsHelper;
        $this->urlInterface = $urlInterface;
        $this->permissionChecker = $permissionChecker;
        $this->checoutSession = $checoutSession;
        parent::__construct($context, $data);
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
                ->createBlock(\Magento\Theme\Block\Html\Pager::class, 'bss.sales.order.history.waiting.pager')
                ->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])
                ->setShowPerPage(true)
                ->setCollection($this->getQuotes());
            $this->setChild('pager', $pager);
            $this->getQuotes()->load();
        }
        $this->createNewBackQuote();
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
     * @return Phrase
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
     */
    public function actionBy($subId)
    {
        return $this->tabsHelper->actionBy($subId);
    }

    /**
     * Allow sub user approve order
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function approveOrder()
    {
        return $this->tabsHelper->approveOrder();
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
     * Function get view url
     *
     * @param $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('companyaccount/order/view', ['order_id' => $order->getQuoteId()]);
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
     * Format date
     *
     * @param $time
     * @return string
     * @throws Exception
     */
    public function getFormatDate($time)
    {
        return $this->tabsHelper->getFormatDate($time);
    }

    /**
     * Get customer quotes
     *
     * @return Collection|false
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getQuotes()
    {
        return $this->tabsHelper->getQuotes('waiting');
    }

    /**
     * Function check sub user can place order
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canPlaceOrder(): bool
    {
        if ($this->permissionChecker->isDenied(Permissions::PLACE_ORDER)) {
            return false;
        } else {
            return true;
        }
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
     * Create back quote after send request
     *
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createNewBackQuote()
    {
        return $this->checoutSession->getQuote();
    }

    /**
     * Return tab url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('sales/order/history', ['tab' => 'waiting']);
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
            array('_query' => array(
                'field' => $field,
                'sort' => $this->setSortOrder()),
                'tab' => 'waiting'
            )
        );
    }

    /**
     * Check active tab
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isTabActive()
    {
        $name = $this->getRequest()->getParam('tab');
        return $name == 'waiting' || $this->canApproveOrderWaitingOnly();
    }

    /**
     * Check permission approve order waiting
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canApproveOrderWaitingOnly(): bool
    {
        if ($this->permissionChecker->isDenied(Permissions::PLACE_ORDER)
            && !$this->permissionChecker->isDenied(Permissions::APPROVE_ORDER_WAITING)) {
            return true;
        } else {
            return false;
        }
    }
}
