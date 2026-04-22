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
namespace Bss\QuoteExtension\Plugin\Model\Quote;

/**
 * Class Item
 * @package Bss\QuoteExtension\Plugin\Model\Quote
 */
class Item
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Item constructor.
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param $option
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddOption(
        \Magento\Quote\Model\Quote\Item $subject,
        $option
    ) {
        if (is_array($option) &&
            isset($option['product_id']) &&
            isset($option['code']) &&
            $option['code'] == 'product_price' &&
            isset($option['value']) &&
            is_array($option['value'])
        ) {
            $option['value'] = $this->serializer->serialize($option['value']);
        }
        return [$option];
    }
}
