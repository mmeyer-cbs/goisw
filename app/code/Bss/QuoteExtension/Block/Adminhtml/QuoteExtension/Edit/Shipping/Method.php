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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Shipping;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml quote extension view shipping method block
 *
 * Class Method
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Shipping
 */
class Method extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Quote create
     * @var \Magento\Quote\Model\Quote
     */
    protected $quoteCreate;

    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * AbstractView constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Quote\Model\Quote $quoteCreate
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Quote\Model\Quote $quoteCreate,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->quoteCreate = $quoteCreate;
        $this->coreRegistry = $registry;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $data
        );
    }

    /**
     * Retrieve create quote model object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getCreateQuoteModel()
    {
        return $this->quoteCreate;
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        if ($quoteId) {
            return $this->quoteCreate->load($quoteId);
        } else {
            return $this->coreRegistry->registry('mage_quote');
        }
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Shipping Method');
    }

    /**
     * Get header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-shipping-method';
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quote_extension_quote_view_shipping_method');
    }
}
