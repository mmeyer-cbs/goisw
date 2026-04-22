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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Quote\Model\QuoteFactory;

/**
 * Order view form
 *
 * @api
 * @author  Magento Core Team <core@magentocommerce.com>
 * @since   100.0.2
 */
class Info extends Template
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var SubUserQuoteRepository
     */
    protected $subUserQuote;

    /**
     * @var TabsOrder
     */
    protected $tabsHelper;

    /**
     * @param TemplateContext $context
     * @param QuoteFactory $quoteFactory
     * @param SubUserQuoteRepository $subUserQuote
     * @param TabsOrder $tabsHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext         $context,
        QuoteFactory            $quoteFactory,
        SubUserQuoteRepository  $subUserQuote,
        TabsOrder               $tabsHelper,
        array                   $data = []
    ) {
        $this->_isScopePrivate = true;
        $this->quoteFactory = $quoteFactory;
        $this->subUserQuote = $subUserQuote;
        $this->tabsHelper = $tabsHelper;
        parent::__construct($context, $data);
    }

    /**
     * Function prepare layout
     *
     * @return Info|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getQuote()->getId()));
    }

    /**
     * Function load data quote by id
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        return $this->quoteFactory->create()->load($quoteId);
    }

    /**
     * Function get sub quote
     *
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserQuoteInterface|\Bss\CompanyAccount\Model\SubUserQuote
     */
    public function getSubQuote()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        return $this->subUserQuote->getByQuoteId($quoteId);
    }

    /**
     * Display status of approve tab
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getQuoteStatus()
    {
        $quoteStatus = $this->getSubQuote()->getQuoteStatus();
        if ($quoteStatus == 'approved') {
            return $this->tabsHelper->getApproveStatus($this->getRequest()->getParam('order_id'));
        } else {
            return $quoteStatus;
        }
    }
}
