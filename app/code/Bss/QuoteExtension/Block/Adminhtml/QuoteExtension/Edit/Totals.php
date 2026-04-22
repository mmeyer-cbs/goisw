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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Totals
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals
{

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Quote Repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * helper
     *
     * @var \Bss\QuoteExtension\Helper\Admin\Edit\Totals
     */
    protected $helperTotals;

    /**
     * Default renderer
     * @var string
     */
    protected $_defaultRenderer = Totals\DefaultTotals::class;

    /**
     * Totals constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\QuoteExtension\Helper\Admin\Edit\Totals $helperTotals
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Framework\Registry $registry,
        \Bss\QuoteExtension\Helper\Admin\Edit\Totals $helperTotals,
        array $data
    ) {
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $salesData,
            $salesConfig,
            $data
        );
        $this->registry = $registry;
        $this->helperTotals = $helperTotals;
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Quote Totals');
    }

    /**
     * Check allow to send new quote confirmation email
     *
     * @return bool
     */
    public function canSendNewQuoteConfirmationEmail()
    {
        return false;
    }

    /**
     * Retrieve formated price
     *
     * @param float $value
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatPrice($value)
    {
        return $this->helperTotals->formatPrice($value);
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        if (!$quote = $this->registry->registry('mage_quote')) {
            $quoteId = $this->getRequest()->getParam('quote_id');
            if (!$quoteId) {
                return false;
            }
            $this->registry->register(
                'mage_quote',
                $this->helperTotals->getQuoteById($quoteId)
            );
        }

        return $quote;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quote_extension_quote_view_totals');
    }
}
