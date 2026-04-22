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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Bundle\Product;

/**
 * Class Type
 *
 * @package Bss\HidePrice\Plugin\Bundle\Product
 */
class Type
{
    /**
     * Data
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serialize;

    /**
     * Type constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->helper = $helper;
        $this->serialize = $serialize;
    }

    /**
     * Check hide price product before add product to cart
     *
     * @param \Magento\Bundle\Model\Product\Type $subject
     * @param $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeCheckProductBuyState(
        \Magento\Bundle\Model\Product\Type $subject,
        $product
    ) {
        if ($this->helper->isEnable()) {
            $optionIds = $product->getCustomOption('bundle_option_ids');
            $optionIds = $this->serialize->unserialize($optionIds->getValue());
            $optionsCollection = $subject->getOptionsCollection($product);
            foreach ($optionsCollection->getItems() as $option) {
                if (!in_array($option->getOptionId(), $optionIds)) {
                    $option->setRequired(false);
                }
            }
        }
    }
}
