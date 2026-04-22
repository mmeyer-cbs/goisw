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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Block\Product\View\Type;

use Bss\HidePrice\Model\Config\Source\ApplyForChildProduct;

class Configurable
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serialize;

    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->serialize = $serialize;
        $this->helper = $helper;
    }

    /**
     * Add data hide price into child product configurable
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfig($subject, $result)
    {
        if ($this->helper->isEnable()) {
            $product = $subject->getProduct();
            if ($product->getHidepriceApplychild() === ApplyForChildProduct::BSS_HIDE_PRICE_NO) {
                $childProduct = $this->helper->getAllData($product);
                $config = $this->serialize->unserialize($result);
                $config["hidePrice"] = $childProduct;
                $config = $this->removePriceConfig($subject, $config);
                return $this->serialize->serialize($config);
            }
        }
        return $result;
    }

    /**
     * Remove json price on source code
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param array $config
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function removePriceConfig($subject, $config)
    {
        $product = $subject->getProduct();
        if ($product->getHidepriceApplychild() !== ApplyForChildProduct::BSS_HIDE_PRICE_NO) {
            if ($this->helper->activeHidePrice($product)) {
                if ($this->helper->hidePriceActionActive($product) == 1) {
                    // unset child of configurable product price option
                    foreach ($config['optionPrices'] as $key => $optionPrice) {
                        $config['optionPrices'][$key]['oldPrice']['amount'] = 0;
                        $config['optionPrices'][$key]['basePrice']['amount'] = 0;
                        $config['optionPrices'][$key]['finalPrice']['amount'] = 0;
                        $config['optionPrices'][$key]['tierPrices']['amount'] = 0;
                        $config['optionPrices'][$key]['msrpPrice']['amount'] = 0;
                    }
                    unset($config['prices']);
                }
            } else {
                foreach ($subject->getAllowProducts() as $product) {
                    if ($this->helper->activeHidePrice($product)) {
                        if ($this->helper->hidePriceActionActive($product) == 1) {
                            if (isset($config['optionPrices'][$product->getId()])) {
                                $config['optionPrices'][$product->getId()]['oldPrice']['amount'] = 0;
                                $config['optionPrices'][$product->getId()]['basePrice']['amount'] = 0;
                                $config['optionPrices'][$product->getId()]['finalPrice']['amount'] = 0;
                                $config['optionPrices'][$product->getId()]['tierPrices']['amount'] = 0;
                                $config['optionPrices'][$product->getId()]['msrpPrice']['amount'] = 0;
                            }
                        }
                    }
                }
            }
        }
        return $config;
    }
}
