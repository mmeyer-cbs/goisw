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
use Bss\CompanyAccount\Model\ResourceModel\SubUserQuote\CollectionFactory as SubQuoteFactory;
use Bss\CompanyAccount\Model\SubUserFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Tabs Rejected Order History
 *
 * @api
 * @since 100.0.2
 */
class Reject extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SubUserFactory
     */
    protected $subUserFactory;

    /**
     * @var SubQuoteFactory
     */
    protected $subQuoteFactory;

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
     * @param Context $context
     * @param Session $customerSession
     * @param SubQuoteFactory $subQuoteFactory
     * @param SubUserFactory $subUserFactory
     * @param Data $helper
     * @param TabsOrder $tabsHelper
     * @param UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session          $customerSession,
        SubQuoteFactory  $subQuoteFactory,
        SubUserFactory   $subUserFactory,
        Data             $helper,
        TabsOrder        $tabsHelper,
        UrlInterface     $urlInterface,
        array            $data = []
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->subQuoteFactory = $subQuoteFactory;
        $this->subUserFactory = $subUserFactory;
        $this->tabsHelper = $tabsHelper;
        $this->urlInterface = $urlInterface;
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
        return $this->tabsHelper->getQuotes('rejected');
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
                ->createBlock(\Magento\Theme\Block\Html\Pager::class, 'bss.sales.order.history.reject.pager')
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
     * @param $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('companyaccount/order/view', ['order_id' => $order->getQuoteId()]);
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
    public function getSortIconRejectedBy()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'action_by'))
            ? $this->tabsHelper->getSortIcon() : '';
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
        return $this->getUrl('sales/order/history', ['tab' => 'reject']);
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
                'tab' => 'reject'
            )
        );
    }

    /**
     * @return bool
     */
    public function isTabActive()
    {
        $name = $this->getRequest()->getParam('tab');
        return $name == 'reject';
    }
}
