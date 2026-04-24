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
 * @package    Bss_AddMultipleProducts
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AddMultipleProducts\Block;

/**
 * Class OptionProduct
 *
 * @package Bss\AddMultipleProducts\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionProduct extends Product
{
    /**
     * @param \Magento\Catalog\Model\ProductFactory
     */
    protected $productloader;

    /**
     * @var \Magento\Bundle\Model\Option
     */
    protected $bundleOption;

    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $bundleProductType;

    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $productOption;

    /**
     * @var \Bss\AddMultipleProducts\Helper\Data
     */
    protected $helperBss;

    /**
     * OptionProduct constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Bundle\Model\Option $bundleOption
     * @param \Magento\Bundle\Model\Product\Type $bundleProductType
     * @param \Magento\Catalog\Model\Product\Option $productOption
     * @param \Bss\AddMultipleProducts\Helper\Data $helperBss
     * @param \Magento\Directory\Model\PriceCurrency
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Bundle\Model\Option $bundleOption,
        \Magento\Bundle\Model\Product\Type $bundleProductType,
        \Magento\Catalog\Model\Product\Option $productOption,
        \Bss\AddMultipleProducts\Helper\Data $helperBss,
        \Magento\Directory\Model\PriceCurrency $priceCurrency
    ) {
        $this->productloader = $productloader;
        $this->bundleOption = $bundleOption;
        $this->bundleProductType = $bundleProductType;
        $this->productOption = $productOption;
        $this->helperBss = $helperBss;
        parent::__construct(
            $context,
            $resultPageFactory,
            $pricingHelper,
            $stockRegistry,
            $catalogProductOptionTypeDate,
            $priceCurrency
        );
    }

    /**
     * @param $product
     * @param bool $configurable
     * @return string
     */
    public function getProductOptionsHtml($product, $configurable = false)
    {
        $blockOption = $this->getLayout()->createBlock(\Magento\Catalog\Block\Product\View\Options::class);
        $optionsHtml = null;

        if ($product->getTypeId() == "simple"
            || $product->getTypeId() == "virtual"
            || $product->getTypeId() == "configurable"
            || $product->getTypeId() == "downloadable"
            || $product->getTypeId() == "bundle"
        ) {
            $blockOption->setProduct($product);
            $customOptions = $this->productOption->getProductOptionCollection($product);
            if ($customOptions) {
                foreach ($blockOption->getOptions() as $_option) {
                    $optionsHtml .= $this->getValuesHtml($_option, $product);
                }
            }
        }
        $optionsHtml = $this->getBlockOptionHtml($optionsHtml, $product, $configurable);
        return '<div class="fieldset" tabindex="0">' . $optionsHtml . '</div>';
    }

    /**
     * @param $optionsHtml
     * @param $product
     * @param $configurable
     * @return string
     */
    protected function getBlockOptionHtml($optionsHtml, $product, $configurable)
    {
        if ($product->getTypeId() == "bundle") {
            $optionsHtml .= $this->getOptionsBundleProduct($product);
        }
        if ($product->getTypeId() == "downloadable") {
            $blockViewType = $this->getLayout()->createBlock(\Magento\Downloadable\Block\Catalog\Product\Links::class);
            $blockViewType->setProduct($product);
            $blockViewType->setTemplate("Magento_Downloadable::catalog/product/links.phtml");
            $optionsHtml .= $blockViewType->toHtml();
        }
        if ($product->getTypeId() == "grouped") {
            $blockViewType = $this->getLayout()->createBlock(\Magento\GroupedProduct\Block\Product\View\Type\Grouped::class);
            $blockViewType->setProduct($product);
            $blockViewType->setTemplate("Magento_GroupedProduct::product/view/type/grouped.phtml");
            $optionsHtml .= $blockViewType->toHtml();
        }

        if ($product->getTypeId()=="configurable" && $configurable) {
            $blockViewType = $this->getLayout()->createBlock(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable::class);
            $blockViewType->setProduct($product);
            $blockViewType->setTemplate("Bss_AddMultipleProducts::product/view/type/options/configurable.phtml");
            $optionsHtml .= $blockViewType->toHtml();
        }
        return $optionsHtml;
    }

    // bundle option product

    /**
     * @param $product
     * @return string
     */
    public function getOptionsBundleProduct($product)
    {
        $optionsHtml = '';
        $store_id = $this->_storeManager->getStore()->getId();

        $options = $this->bundleOption->getResourceCollection()
            ->setProductIdFilter($product->getId())
            ->setPositionOrder();

        $options->joinValues($store_id);
        $typeInstance = $this->bundleProductType;

        $_selections = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
        $blockOptionbl = $this->getLayout()->createBlock(\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option::class);
        $blockOptionbl->setProduct($product);

        $price = $product->getPriceInfo()->getPrice('bundle_option');
        foreach ($options as $_option) {
            if ($_option->getType() == 'checkbox') {
                $optionsHtml .= $this->getBOptionCheckbox($_option, $_selections, $blockOptionbl, $price);
            }
            if ($_option->getType() == 'multi') {
                $optionsHtml .= $this->getBOptionMulti($_option, $_selections, $blockOptionbl, $price);
            }
            if ($_option->getType() == 'radio') {
                $optionsHtml .= $this->getBOptionRadio($_option, $_selections, $blockOptionbl, $price);
            }
            if ($_option->getType() == 'select') {
                $optionsHtml .= $this->getBOptionSelect($_option, $_selections, $blockOptionbl, $price);
            }
        }

        return $optionsHtml;
    }

    /**
     * @param $_option
     * @param $_selections
     * @param $blockOptionbl
     * @param $price
     * @return string
     */
    public function getBOptionCheckbox($_option, $_selections, $blockOptionbl, $price)
    {
        $amount = 0;
        $optionsHtml = '';
        $optionsHtml.='<div class="field option ';
        if ($_option->getRequired()) {
            $optionsHtml.= 'required';
        }
        $optionsHtml.='">';
        $optionsHtml.='<label class="label"><span>' . $this->escapeHtml($_option->getTitle()) . '</span></label>';
        $optionsHtml.='<div class="control">
                <div class="nested options-list">';
        foreach ($_selections as $_selection) {
            if ($_selection->getOptionId() == $_option->getId()) {
                $optionsHtml.= '<div class="field choice">
                                     <input class="bundle-option-' . $_option->getId();
                $optionsHtml.= ' checkbox product bundle option change-container-classname" id="bundle-option-';
                $optionsHtml.= $_option->getId() . '-' . $_selection->getSelectionId() . '" type="checkbox"';
                if ($_option->getRequired()) {
                    $optionsHtml.= 'data-validate="{';
                    $optionsHtml.= '\'validate-one-required-by-name\':\'input[name*=&quot;bundle_option[';
                    $optionsHtml.= $_option->getId() . ']&quot;]:checked\'}"';
                }
                $optionsHtml.= 'name="bundle_option[' . $_option->getId() . '][' . $_selection->getId() . ']"';
                $optionsHtml.= 'data-selector="bundle_option[' . $_option->getId() . '][' . $_selection->getId() . ']"';

                $optionsHtml = $this->hasSeclected($blockOptionbl, $_selection, $optionsHtml);

                $optionsHtml = $this->hasDisabled($_selection, $optionsHtml);
                $amount = $price->getOptionSelectionAmount($_selection)->getValue();
                $qty = (int)$_selection->getSelectionQty();
                $item_price = $amount * $qty;
                $optionsHtml.= 'value="' . $_selection->getSelectionId() . '" price="' . $item_price . '" />';
                $optionsHtml.= '<label class="label"';
                $optionsHtml.= 'for="bundle-option-' . $_option->getId() . '-' . $_selection->getSelectionId() . '">';
                $optionsHtml.= '<span>' . $blockOptionbl->getSelectionQtyTitlePrice($_selection) . '</span>';
                $optionsHtml.= '</label></div>';
            }
        }
        $optionsHtml.='<div id="bundle-option-' . $_option->getId() . '-container"></div>
                </div>
            </div>
        </div>';
        return $optionsHtml;
    }

    /**
     * @param $_option
     * @param $_selections
     * @param $blockOptionbl
     * @param $price
     * @return string
     */
    public function getBOptionMulti($_option, $_selections, $blockOptionbl, $price)
    {
        $amount = 0;
        $optionsHtml = '';
        $optionsHtml.='<div class="field option ';
        if ($_option->getRequired()) {
            $optionsHtml.= 'required';
        }
        $optionsHtml.='">';
        $optionsHtml.='<label class="label"><span>' . $this->escapeHtml($_option->getTitle()) . '</span></label>';
        $optionsHtml.='<div class="control">';

        $optionsHtml.='<select multiple="multiple"
                            size="5"
                            id="bundle-option-' . $_option->getId() . '"
                            name="bundle_option[' . $_option->getId() . '][]"
                            data-selector="bundle_option[' . $_option->getId() . '][]"';
        $optionsHtml.= 'class="bundle-option-' . $_option->getId();
        $optionsHtml.= ' multiselect product bundle option change-container-classname"';

        if ($_option->getRequired()) {
            $optionsHtml.='data-validate={required:true}';
        }
        $optionsHtml.='>';
        if (!$_option->getRequired()) {
            $optionsHtml.='<option value="">' . __('None') . '</option>';
        }
        foreach ($_selections as $_selection) {
            if ($_selection->getOptionId() == $_option->getId()) {
                $amount = $price->getOptionSelectionAmount($_selection)->getValue();
                $qty = (int)$_selection->getSelectionQty();
                $item_price = $amount * $qty;
                $optionsHtml.='<option value="' . $_selection->getSelectionId() . '" price="' . $item_price . '"';

                $optionsHtml = $this->hasSeclected($blockOptionbl, $_selection, $optionsHtml);

                $optionsHtml = $this->hasDisabled($_selection, $optionsHtml);

                $optionsHtml.='>';
                $optionsHtml.= $blockOptionbl->getSelectionQtyTitlePrice($_selection, false);
                $optionsHtml.='</option>';
            }
        }
        $optionsHtml.='</select></div></div>';
        return $optionsHtml;
    }

    /**
     * @param $_option
     * @param $_selections
     * @param $blockOptionbl
     * @param $price
     * @return string
     */
    public function getBOptionRadio($_option, $_selections, $blockOptionbl, $price)
    {
        $amount = 0;
        $optionsHtml = '';
        $_default = $_option->getDefaultSelection();
        $optionsHtml.='<div class="field option ';
        if ($_option->getRequired()) {
            $optionsHtml.= 'required';
        }
        $optionsHtml.='">';
        $optionsHtml.='<label class="label"><span>' . $this->escapeHtml($_option->getTitle()) . '</span></label>';
        $optionsHtml.='<div class="control">
                <div class="nested options-list">';

        if (!$_option->getRequired()) {
            $optionsHtml.='<div class="field choice">
                                    <input type="radio"
                                           class="radio product bundle option"
                                           id="bundle-option-' . $_option->getId() . '"
                                           name="bundle_option[' . $_option->getId() . ']"
                                           data-selector="bundle_option[' . $_option->getId() . ']"';
            if (!$_default || !$_default->isSalable()) {
                $optionsHtml.='checked="checked" ';
            }
            $optionsHtml.='value=""/>
                                    <label class="label" for="bundle-option-' . $_option->getId() . '">
                                        <span>' . __('None') . '</span>
                                    </label>
                                </div>';
        }

        foreach ($_selections as $_selection) {
            if ($_selection->getOptionId() == $_option->getId()) {
                $optionsHtml.='<div class="field choice">
                                    <input type="radio"
                                           class="radio product bundle option change-container-classname"';
                $optionsHtml.= 'id="bundle-option-' . $_option->getId() . '-' . $_selection->getSelectionId() . '"';
                if ($_option->getRequired()) {
                    $optionsHtml.='data-validate="{\'validate-one-required-by-name\':true}"';
                }
                $optionsHtml.='name="bundle_option[' . $_option->getId() . ']"
                                           data-selector="bundle_option[' . $_option->getId() . ']"';
                $optionsHtml = $this->hasSeclected($blockOptionbl, $_selection, $optionsHtml);

                $optionsHtml = $this->hasDisabled($_selection, $optionsHtml);
                // handle price
                $amount = $price->getOptionSelectionAmount($_selection)->getValue();
                $item_price = $amount;
                $default_qty = (int)$_selection->getSelectionQty();
                if (!$_selection->getSelectionCanChangeQty()) {
                    $item_price = $amount * $default_qty;
                }

                $optionsHtml .= 'value="' . $_selection->getSelectionId() . '"  price="' . $item_price . '"'
                    . 'can-change-qty="' . $_selection->getSelectionCanChangeQty() . '"'
                    . 'default-qty="' . $default_qty . '"'
                    . '" />';
                $optionsHtml.= '<label class="label" for="bundle-option-' . $_option->getId();
                $optionsHtml.= '-' . $_selection->getSelectionId() . '">
                                    <span>' . $blockOptionbl->getSelectionTitlePrice($_selection) . '</span>
                                </label>
                            </div>';
            }
        }
        $optionsHtml.='<div id="bundle-option-' . $_option->getId() . '-container"></div>';

        $optionsHtml.='<div class="field qty qty-holder">
                        <label class="label" for="bundle-option-' . $_option->getId() . '-qty-input">
                            <span>' . __('Quantity') . '</span>
                        </label>';
        $optionsHtml.='<div class="control">
                            <input id="bundle-option-' . $_option->getId() . '-qty-input"
                                   class="input-text qty bundle"
                                   type="number"
                                   name="bundle_option_qty[' . $_option->getId() . ']"
                                   data-selector="bundle_option_qty[' . $_option->getId() . ']"
                                   value="1" style="width: 3.2em;"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        return $optionsHtml;
    }

    /**
     * @param $_option
     * @param $_selections
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $blockOptionbl
     * @param $price
     * @return string
     */
    public function getBOptionSelect($_option, $_selections, $blockOptionbl, $price)
    {
        $amount = 0;
        $optionsHtml = '';
        $optionsHtml.='<div class="field option ';
        if ($_option->getRequired()) {
            $optionsHtml.= 'required';
        }
        $optionsHtml.= '">';
        $optionsHtml.= '<label class="label"><span>' . $this->escapeHtml($_option->getTitle()) . '</span></label>';
        $optionsHtml.= '<div class="control">';

        $optionsHtml.= '<select id="bundle-option-' . $_option->getId() . '"
                            name="bundle_option[' . $_option->getId() . ']"
                            data-selector="bundle_option[' . $_option->getId() . ']"';
        $optionsHtml.= 'class="bundle-option-' . $_option->getId();
        $optionsHtml.= ' product bundle option bundle-option-select change-container-classname"';
        if ($_option->getRequired()) {
            $optionsHtml.= 'data-validate = {required:true}';
        }
        $optionsHtml.='>';
        $optionsHtml.='<option value="">' . __('Choose a selection...') . '</option>';
        foreach ($_selections as $_selection) {
            if ($_selection->getOptionId() == $_option->getId()) {
                // handle price
                $amount = $price->getOptionSelectionAmount($_selection)->getValue();
                $item_price = $amount;
                $default_qty = (int)$_selection->getSelectionQty();
                if (!$_selection->getSelectionCanChangeQty()) {
                    $item_price = $amount * $default_qty;
                }
                $optionsHtml.='<option value="' . $_selection->getSelectionId() . '" price="' . $item_price . '"';

                $optionsHtml = $this->hasSeclected($blockOptionbl, $_selection, $optionsHtml);

                $optionsHtml = $this->hasDisabled($_selection, $optionsHtml);

                $optionsHtml .= ' can-change-qty="' . $_selection->getSelectionCanChangeQty() . '"';
                $optionsHtml .= 'default-qty="' . $default_qty . '"';
                $renderPrice = strip_tags($blockOptionbl->getSelectionTitlePrice($_selection, false))
                    ?? '';
                $arr = explode('+', $renderPrice);

                $label = $renderPrice;
                if ($arr[1]) {
                    $arrr = explode(' ', $arr[1]);
                    $arrr = array_values(array_filter($arrr, function ($value) {
                        return trim($value) !== '';
                    }));
                    if (isset($arrr[1]) && $arrr[1]) {
                        if ($arrr[1] != $arrr[0]) {
                            $label = str_replace($arrr[1], __('(Excl. tax: %1)', trim($arrr[1])), $label);
                        } else {
                            $label = trim($arr[0])
                                . ' + '
                                . $arrr[1]
                                . ' '
                                . __('(Excl. tax: %1)', trim($arrr[1]));
                        }
                    }
                }
                $optionsHtml.='>' . $label . '</option>';
            }
        }
        $optionsHtml.='</select>';

        $optionsHtml.='<div class="nested">
                    <div class="field qty qty-holder">
                        <label class="label" for="bundle-option-' . $_option->getId() . '-qty-input">
                            <span>' . __('Quantity') . '</span>
                        </label>
                        <div class="control">
                            <input id="bundle-option-' . $_option->getId() . '-qty-input"
                                   class="input-text qty bundle"
                                   type="number"
                                   name="bundle_option_qty[' . $_option->getId() . ']"
                                   data-selector="bundle_option_qty[' . $_option->getId() . ']"
                                   value="1"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        return $optionsHtml;
    }

    /**
     * @param $blockOptionbl
     * @param $_selection
     * @param $optionsHtml
     * @return string
     */
    protected function hasSeclected($blockOptionbl, $_selection, $optionsHtml)
    {
        if ($blockOptionbl->isSelected($_selection)) {
            $optionsHtml.=' selected="selected"';
        }
        return $optionsHtml;
    }

    /**
     * @param $_selection
     * @param $optionsHtml
     * @return string
     */
    protected function hasDisabled($_selection, $optionsHtml)
    {
        if (!$_selection->isSaleable()) {
            $optionsHtml.=' disabled="disabled"';
        }
        return $optionsHtml;
    }

    /**
     * @param $_option
     * @param $product
     * @return string
     */
    public function getValuesHtml($_option, $product)
    {
        $configValue = $product->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $product->getStore();

        $class = ($_option->getIsRequire()) ? ' required' : '';
        $html = '';

        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FIELD ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA
        ) {
            $html =  $this->getCustomOptionText($product, $_option, $class, $configValue, $store);
        }
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME
        ) {
            $html =  $this->getCustomOptionTime($product, $_option, $class, $configValue, $store);
        }

        return $this->getValuesTypeHtml($html, $product, $_option, $class, $configValue, $store);
    }

    /**
     * @param $html
     * @param $product
     * @param $_option
     * @param $class
     * @param $configValue
     * @param $store
     * @return string
     */
    protected function getValuesTypeHtml($html, $product, $_option, $class, $configValue, $store)
    {
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
        ) {
            $html =  $this->getCustomOptionDropdownMuiltiple($product, $_option, $class, $configValue, $store);
        }

        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX
        ) {
            $html =  $this->getCustomOptionRadioCheckbox($product, $_option, $class, $configValue, $store);
        }
        return $html;
    }

    // get custom option
    /**
     * @param $product
     * @param $_option
     * @param $class
     * @param $configValue
     * @param $store
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCustomOptionText($product, $_option, $class, $configValue, $store)
    {
        $html = '';
        $html .= '<div class="field';
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA) {
            $html .= ' textarea ';
        }
        $html .= $class . '">';
        $html .='<label class="label" for="options_' . $_option->getId() . '_text">
        <span>' . $this->escapeHtml($_option->getTitle()) . '</span>
        </label>';
        $priceStr = $this->formatPrice(
            $_option,
            $product,
            [
                'is_percent' => $_option->getPriceType() == 'percent',
                'pricing_value' => $_option->getPrice($_option->getPriceType() == 'percent'),
            ],
            false
        );

        $html .='<div class="control">';
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FIELD) {
            $html .= $this->getCustomOptionTextFiled($product, $_option, $store);
        }
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA) {
            $html .= $this->getCustomOptionTextArea($product, $_option, $store);
        }
        if ($_option->getMaxCharacters()) {
            $html .='<p class="note">Maximum number of characters:
                <strong>' . $_option->getMaxCharacters() . '</strong></p>';
        }
        $html .='<label class="label admin__field-label" for="options_' .
            $_option->getId() . '_' .
            '"><span>' . '</span> ' . $priceStr . '</label>';
        $html .='</div></div>';
        return $html;
    }

    /**
     * @param $product
     * @param $_option
     * @param $class
     * @param $configValue
     * @param $store
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCustomOptionTime($product, $_option, $class, $configValue, $store)
    {
        $html = '';
        $html .='<div class="field date' . $class . '"';
        $html .='">
            <fieldset class="fieldset fieldset-product-options-inner' . $class . '">
                <legend class="legend">
                    <span>' . $this->escapeHtml($_option->getTitle()) . '</span>
                </legend>';
        $html .='<div class="control">';
        $priceStr = $this->formatPrice(
            $_option,
            $product,
            [
                'is_percent' => $_option->getPriceType() == 'percent',
                'pricing_value' => $_option->getPrice($_option->getPriceType() == 'percent'),
            ],
            false
        );

        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME
            || $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE) {
            $html .= $this->getDateHtml($_option, $product);
        }

        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME
            || $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME) {
            $html .= $this->getTimeHtml($_option, $product);
        }

        if ($_option->getIsRequire()) {
            $html .='<input type="hidden"
                               name="validate_datetime_' . $_option->getId() . '"
                               class="validate-datetime-' . $_option->getId() . '"
                               price="' . $this->getCurrentStore($_option, $store) . '"
                               value=""
                               data-validate="{"validate-required-datetime":' . $_option->getId() . '}"/>';
        } else {
            $html .='<input type="hidden"
                               name="validate_datetime_' . $_option->getId() . '"
                               class="validate-datetime-' . $_option->getId() . '"
                               price="' . $this->getCurrentStore($_option, $store) . '"
                               value=""
                               data-validate="{"validate-optional-datetime":' . $_option->getId() . '}"/>';
        }

        $html .='<label class="label admin__field-label" for="options_' .
            $_option->getId() . '_' .
            '"><span>' . '</span> ' . $priceStr . '</label>';

        $html .='</div></fieldset></div>';
        return $html;
    }

    /**
     * @param $product
     * @param $_option
     * @param $class
     * @param $configValue
     * @param $store
     * @return string
     */
    public function getCustomOptionDropdownMuiltiple($product, $_option, $class, $configValue, $store)
    {
        $html = '';
        $html .= '<div class="field' . $class . '">';
        $html .='<label class="label" for="select_' . $_option->getId() . '">
                <span>' . $this->escapeHtml($_option->getTitle()) . '</span>
                </label>';
        $html .='<div class="control">';
        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => 'select_' . $_option->getId(),
                'class' => $class . ' product-custom-option admin__control-select'
            ]
        );
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN) {
            $select->setName('options[' . $_option->getid() . ']')->addOption('', __('-- Please Select --'));
        } else {
            $select->setName('options[' . $_option->getid() . '][]');
            $select->setClass('multiselect admin__control-multiselect' . $class . ' product-custom-option');
        }
        foreach ($_option->getValues() as $_value) {
            $priceStr = $this->formatPrice(
                $_option,
                $product,
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ],
                false
            ) ?? '';

            $arr = explode(' ', strip_tags($priceStr));
            $arr = array_values(array_filter($arr, function ($value) {
                return trim($value) !== '';
            }));
            $priceText = strip_tags($priceStr);
            if (isset($arr[2])) {
                $priceText = str_replace($arr[2], '(Excl. tax: ' . trim($arr[2]) . ')', $priceText);
            }
            $select->addOption(
                $_value->getOptionTypeId(),
                $_value->getTitle() . ' ' . $priceText . '',
                ['price' => $this->getCurrentStore($_value, $store)]
            );
        }
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE) {
            $extraParams = ' multiple="multiple"';
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        if ($configValue) {
            $select->setValue($configValue);
        }
        $html .= $select->getHtml();
        $html .='</div></div>';
        return $html;
    }

    /**
     * @param $product
     * @param $_option
     * @param $class
     * @param $configValue
     * @param $store
     * @return string
     */
    public function getCustomOptionRadioCheckbox($product, $_option, $class, $configValue, $store)
    {
        $html = '';
        $html .= '<div class="field' . $class . '">';
        $html .='<label class="label" for="select_' . $_option->getId() . '">
                <span>' . $this->escapeHtml($_option->getTitle()) . '</span>
                </label>';
        $html .='<div class="control">';
        $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
        $arraySign = '';
        $type = '';
        $class = '';
        $classs = '';
        switch ($_option->getType()) {
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO:
                $type = 'radio';
                $classs = 'radio admin__control-radio';
                if (!$_option->getIsRequire()) {
                    $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                        '<input type="radio" id="options_' .
                        $_option->getId() .
                        '" class="' .
                        $classs .
                        ' product-custom-option" name="options[' .
                        $_option->getId() .
                        ']"' .
                        ' data-selector="options[' . $_option->getId() . ']"' .
                        ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                        $_option->getId() .
                        '"><span>' .
                        __('None') . '</span></label></div>';
                }
                break;
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX:
                $type = 'checkbox';
                $classs = 'checkbox admin__control-checkbox';
                $arraySign = '[]';
                break;
        }
        $count = 1;
        foreach ($_option->getValues() as $_value) {
            $count++;

            $priceStr = $this->formatPrice(
                $_option,
                $product,
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ]
            );

            $htmlValue = $_value->getOptionTypeId() ?? [];
            $dataSelector = 'options[' . $_option->getId() . ']';
            if ($arraySign) {
                $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
                $dataSelector .= '[' . $htmlValue . ']';
            } else {
                $checked = $configValue == $htmlValue ? 'checked' : '';
            }

            $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                $class .
                '">' .
                '<input type="' .
                $type .
                '" class="' .
                $classs .
                ' ' .
                $class .
                ' product-custom-option"' .
                ' name="options[' .
                $_option->getId() .
                ']' .
                $arraySign .
                '" id="options_' .
                $_option->getId() .
                '_' .
                $count .
                '" value="' .
                $htmlValue .
                '" ' .
                $checked .
                ' data-selector="' . $dataSelector . '"' .
                ' price="' .
                $this->getCurrentStore($_value, $store) .
                '" />' .
                '<label class="label admin__field-label" for="options_' .
                $_option->getId() . '_' .
                $count . '"><span>' . $_value->getTitle() . '</span> ' . $priceStr . '</label>';
            $selectHtml .= '</div>';
        }
        $selectHtml .= '</div>';
        $html .= $selectHtml;
        $html = $this->customOptionHtml($_option, $html);
        $html .='</div></div>';

        return $html;
    }

    /**
     * @param $_option
     * @param $html
     * @return string
     */
    protected function customOptionHtml($_option, $html)
    {
        if ($_option->getIsRequire()) {
            $html .='<span id="options-' . $_option->getId() . '-container"></span>';
        }
        return $html;
    }

    /**
     * @return string
     */
    public function geturlAddMultipleToCart()
    {
        return $this->getUrl('addmuntiple/cart/addMuntiple');
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Model\Product
     */
    public function getLoadProduct($id)
    {
        return $this->productloader->create()->load($id);
    }

    /**
     * Get Bss heler class
     *
     * @return \Bss\AddMultipleProducts\Helper\Data
     */
    public function getHelperBss()
    {
        return $this->helperBss;
    }
}
