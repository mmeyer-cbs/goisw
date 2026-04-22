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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Shipping\Method\Form;

/**
 * Class QuoteExtension
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Shipping\Method\Form
 */
class QuoteExtension extends \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Shipping\Method\Form
{

    /**
     * Get method title
     *
     * @return string
     */
    public function getMethodTitle()
    {
        if ($this->getRate()->getMethodTitle()) {
            $methodTitle = $this->getRate()->getMethodTitle();
        } else {
            $methodTitle = $this->getRate()->getMethodDescription();
        }

        return $this->escapeHtml($methodTitle);
    }

    /**
     * Get the shipping rate
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate
     */
    public function getRate()
    {
        return $this->getData('rate');
    }

    /**
     * Get tax helper
     *
     * @return \Magento\Tax\Helper\Data
     */
    public function getTaxHelper()
    {
        return $this->getData('tax_helper');
    }

    /**
     * Set tax helper
     *
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @return $this
     */
    public function setTaxHelper(\Magento\Tax\Helper\Data $taxHelper)
    {
        $this->setData('tax_helper', $taxHelper);
        return $this;
    }

    /**
     * Set the shipping rate
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $rate
     * @return $this
     */
    public function setRate(\Magento\Quote\Model\Quote\Address\Rate $rate)
    {
        $this->setData('rate', $rate);
        return $this;
    }

    /**
     * Get the shipping code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Set the shipping code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->setData('code', $code);
        return $this;
    }

    /**
     * Get the shipping button property
     *
     * @return string
     */
    public function getRadioProperty()
    {
        return $this->getData('radio_property');
    }

    /**
     * Set the radio button property
     *
     * @param string $code
     * @return $this
     */
    public function setRadioProperty($code)
    {
        $this->setData('radio_property', $code);
        return $this;
    }
}
