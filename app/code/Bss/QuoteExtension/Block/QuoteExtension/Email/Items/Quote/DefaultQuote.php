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

namespace Bss\QuoteExtension\Block\QuoteExtension\Email\Items\Quote;

/**
 * Class DefaultQuote
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension\Email\Items\Quote
 */
class DefaultQuote extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }

    /**
     * Get options for item
     *
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        $options = $this->getItem()->getProduct()->getTypeInstance()->getOrderOptions($this->getItem()->getProduct());
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    /**
     * Return Html for Item
     *
     * @param string|array $value
     * @return string
     */
    public function getValueHtml($value)
    {
        if (is_array($value)) {
            return sprintf(
                    '%d',
                    $value['qty']
                ) . ' x ' . $this->escapeHtml(
                    $value['title']
                ) . " " . $this->getItem()->getOrder()->formatPrice(
                    $value['price']
                );
        } else {
            return $this->escapeHtml($value);
        }
    }

    /**
     * Get Item Sku
     *
     * @param mixed $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku')) {
            return $item->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }

    /**
     * Return product additional information block
     *
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    /**
     * Get the html for item price
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }
}
