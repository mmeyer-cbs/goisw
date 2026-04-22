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

use Magento\Customer\Model\Context;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\QuoteFactory;

/**
 * Sales order view block
 *
 * @api
 * @since 100.0.2
 */
class View extends Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 101.0.0
     */
    protected $httpContext;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        Template\Context                    $context,
        \Magento\Framework\App\Http\Context $httpContext,
        QuoteFactory                        $quoteFactory,
        array                               $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context, $data);
    }

    /**
     * Function prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order Request # %1', $this->getQuote()->getId()));
    }

    /**
     * Function get payment info html
     *
     * @return string
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        return $this->quoteFactory->create()->load($quoteId);
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('*/*/history');
        }
        return $this->getUrl('*/*/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return \Magento\Framework\Phrase
     */
    public function getBackTitle()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return __('Back to My Orders');
        }
        return __('View Another Order');
    }
}
