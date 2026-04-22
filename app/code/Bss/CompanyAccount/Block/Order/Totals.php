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

use Magento\Directory\Model\Currency;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;

/**
 * Quote totals.
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * Associated array of totals
     * array(
     *  $totalCode => $totalObject
     * )
     *
     * @var array
     */
    protected $totals;

    /**
     * @var Quote|null
     */
    protected $quote = null;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Currency
     */
    protected $quoteCurrency;

    /**
     * @param Context $context
     * @param QuoteFactory $quoteFactory
     * @param Currency $quoteCurrency
     * @param array $data
     */
    public function __construct(
        Context  $context,
        QuoteFactory $quoteFactory,
        Currency $quoteCurrency,
        array    $data = []
    ) {
        $this->quoteCurrency = $quoteCurrency;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize self totals and children blocks totals before html building
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->_initTotals();
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
            if (method_exists($child, 'initTotals') && is_callable([$child, 'initTotals'])) {
                $child->initTotals();
            }
        }
        return parent::_beforeToHtml();
    }

    /**
     * Get quote object
     *
     * @return Quote
     */
    public function getQuote()
    {
        $quote = $this->getRequest()->getParam('order_id');
        if ($this->quote === null) {
            if ($this->hasData('order')) {
                $this->quote = $this->_getData('order');
            } elseif ($this->quoteFactory->create()->load($quote)) {
                $this->quote = $this->quoteFactory->create()->load($quote);
            } elseif ($this->getParentBlock()->getQuote()) {
                $this->quote = $this->getParentBlock()->getQuote();
            }
        }
        return $this->quote;
    }

    /**
     * Sets quote.
     *
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Get totals source object
     *
     * @return Quote
     */
    public function getSource()
    {
        return $this->getQuote();
    }

    /**
     * Initialize quote totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->totals = [];
        $this->totals['subtotal'] = new \Magento\Framework\DataObject(
            ['code' => 'subtotal', 'value' => $source->getSubtotal(), 'label' => __('Subtotal')]
        );

        return $this;
    }

    /**
     * Get Total object by code
     *
     * @param string $code
     * @return mixed
     */
    public function getTotal($code)
    {
        if (isset($this->totals[$code])) {
            return $this->totals[$code];
        }
        return false;
    }

    /**
     * Delete total by specific
     *
     * @param string $code
     * @return  $this
     */
    public function removeTotal($code)
    {
        unset($this->totals[$code]);
        return $this;
    }

    /**
     * Get totals array for visualization
     *
     * @param array|null $area
     * @return array
     */
    public function getTotals($area = null)
    {
        $totals = [];
        if ($area === null) {
            $totals = $this->totals;
        } else {
            $area = (string)$area;
            foreach ($this->totals as $total) {
                $totalArea = (string)$total->getArea();
                if ($totalArea == $area) {
                    $totals[] = $total;
                }
            }
        }
        return $totals;
    }

    /**
     * Format total value based on quote currency
     *
     * @param \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->formatPrice($total->getValue());
        }
        return $total->getValue();
    }

    /**
     * Function format price of quote
     *
     * @param string $total
     * @param bool $addBrackets
     * @return string
     */
    public function formatPrice($total, $addBrackets = false): string
    {
        $quoteCurrency = $this->quoteCurrency;
        return $quoteCurrency->formatPrecision($total, 2, [], true, $addBrackets);
    }
}
