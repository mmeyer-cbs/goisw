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
namespace Bss\QuoteExtension\Plugin\Block\Product;

use Magento\Catalog\Block\Product\View as MagentoView;

/**
 * Class AddtoQuoteButton
 *
 * @package Bss\QuoteExtension\Plugin\Block\Product
 */
class AddtoQuoteButton
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\QuoteExtension\Helper\Admin\ConfigShow
     */
    protected $helperConfig;

    /**
     * AddtoQuoteButton constructor.
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $helper,
        \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig
    ) {
        $this->helper = $helper;
        $this->helperConfig = $helperConfig;
    }

    /**
     * Add AddtoQuote Button
     *
     * @param MagentoView $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        MagentoView $subject,
        $result
    ) {
        $matchedNames = [
            'product.info.addtocart.additional',
            'product.info.addtocart',
            'product.info.addtocart.bundle'
        ];
        $product = $subject->getProduct();
        if (in_array($subject->getNameInLayout(), $matchedNames) && $product->getIsActiveRequest4QuoteProductPage()) {
            $buttonTitle = $this->helperConfig->getProductPageText()
                ? $this->helperConfig->getProductPageText()
                : __('Add to Quote');
            $pattern = '#<button([^>]*)product-addtocart-button([^*]*)<\/button>#';
            preg_match_all($pattern, $result, $_matches);
            $buttonCart = implode('', $_matches[0]);
            if ($product->getDisableAddToCart()) {
                $buttonCart = '';
            }
            $button = '<div class="box-tocart quote_extension' . $product->getId() . '"><button type="button"
                            title="' . $buttonTitle . '"
                            class="action primary toquote"
                            id="product-addtoquote-button">
                            <span>' . $buttonTitle . '</span>
                        </button></div>
                        <script type="text/x-magento-init">
                        {
                            "#product-addtoquote-button": {
                                "Bss_QuoteExtension/js/catalog-add-to-quote": {
                                    "validateQty" : "' . $this->helper->validateQuantity() . '",
                                    "addToQuoteButtonTextDefault" : "' . $buttonTitle . '"
                                }
                            }
                        }
                        </script>';

            $result = preg_replace($pattern, $buttonCart . $button, $result);
        }

        return $result;
    }
}
