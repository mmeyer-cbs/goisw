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
namespace Bss\QuoteExtension\Model\Pdf\Items;

/**
 * Quote Pdf Items renderer Abstract
 */
abstract class AbstractItems extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{

    /**
     * Quote model
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Set order model
     *
     * @param  \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The order object is not specified.'));
        }
        return $this->quote;
    }
}
