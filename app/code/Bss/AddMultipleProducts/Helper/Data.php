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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AddMultipleProducts\Helper;

use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\CalculationFactory;

class Data extends AbstractHelper
{
    const MAGEPLAZA_AJAX_LAYER_ENABLE_CONFIG_XML_PATH = 'layered_navigation/general/ajax_enable';
    const PATH_REQUEST4QUOTE_ENABLED_OTHER_PAGE = 'bss_request4quote/request4quote_product_other_page_config/enable';
    const PATH_REQUEST4QUOTE_ENABLED = 'bss_request4quote/general/enable';
    const PATH_XML_FORBID_CATEGORY_PAGES = 'ajaxmuntiplecart/general/forbid_category_pages';

    /**
     * @var Image
     */
    protected $productImageHelper;

    /**
     * @var CalculationFactory
     */
    protected $calculationFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TaxCalculationInterface
     */
    protected $taxCalculation;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var null
     */
    private $quoteExtensionHelperData;

    public function __construct(
        Context $context,
        Image $productImageHelper,
        TaxCalculationInterface $taxCalculation,
        Session $customerSession,
        CalculationFactory $calculationFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->productImageHelper = $productImageHelper;
        $this->calculationFactory = $calculationFactory;
        $this->storeManager = $storeManager;
        $this->taxCalculation = $taxCalculation;
        $this->customerSession = $customerSession;
        $this->objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int $id
     * @return bool
     */
    public function hasFlagConfig($path, $id = null)
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $id);
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int $id
     * @return string
     */
    public function getValueConfig($path, $id = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $id);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/general/active');
    }

    /**
     * @return array|bool
     */
    public function getCustomerGroup()
    {
        $customer_group = $this->getValueConfig('ajaxmuntiplecart/general/active_for_customer_group') ?? '';
        if ($customer_group != '') {
            return explode(',', $customer_group);
        }
        return false;
    }

    /**
     * Display add multiple cart
     *
     * @return mixed
     */
    public function displayAddMultipleCart()
    {
        return $this->getValueConfig('ajaxmuntiplecart/general/display_addmuntiple');
    }

    /**
     * Display add multiple quote
     *
     * @return mixed
     */
    public function displayAddMultipleQuote()
    {
        return $this->getValueConfig('ajaxmuntiplecart/general/display_add_multiple_quote');
    }

    /**
     * @return mixed
     */
    public function defaultQty()
    {
        return $this->getValueConfig('ajaxmuntiplecart/general/default_qty');
    }

    /**
     * @return mixed
     */
    public function positionButton()
    {
        return $this->getValueConfig('ajaxmuntiplecart/button_grid/position_button');
    }

    /**
     * @return mixed
     */
    public function showTotal()
    {
        return $this->getValueConfig('ajaxmuntiplecart/button_grid/display_total');
    }

    /**
     * @return bool
     */
    public function showSelectProduct()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/button_grid/show_select_product');
    }

    /**
     * @return bool
     */
    public function showStick()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/button_grid/show_stick');
    }

    /**
     * @return mixed
     */
    public function backGroundStick()
    {
        return $this->getValueConfig('ajaxmuntiplecart/button_grid/background_stick');
    }

    /**
     * @return bool
     */
    public function isShowProductImage()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/success_popup/product_image');
    }

    /**
     * @return mixed
     */
    public function getImageSizesg()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/product_image_size_sg');
    }

    /**
     * @return mixed
     */
    public function getImageSizemt()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/product_image_size_mt');
    }

    /**
     * @return mixed
     */
    public function getImageSizeer()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/product_image_size_er');
    }

    /**
     * @return mixed
     */
    public function getItemonslide()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/item_on_slide');
    }

    /**
     * @return mixed
     */
    public function getSlidemove()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/slide_move');
    }

    /**
     * @return mixed
     */
    public function getSlidespeed()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/slide_speed');
    }

    /**
     * @return mixed
     */
    public function getSlideauto()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/slide_auto');
    }

    /**
     * @return bool
     */
    public function isShowProductPrice()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/success_popup/product_price');
    }

    /**
     * @return bool
     */
    public function isShowContinueBtn()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/success_popup/continue_button');
    }

    /**
     * @return mixed
     */
    public function getCountDownActive()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/active_countdown');
    }

    /**
     * @return mixed
     */
    public function getCountDownTime()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/countdown_time');
    }

    /**
     * @return bool
     */
    public function isShowCartInfo()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/success_popup/mini_cart');
    }

    /**
     * @return bool
     */
    public function isShowCheckoutLink()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/success_popup/mini_checkout');
    }

    /**
     * @return bool
     */
    public function isShowSuggestBlock()
    {
        return $this->hasFlagConfig('ajaxmuntiplecart/success_popup/suggest_product');
    }

    /**
     * @return mixed
     */
    public function getSuggestLimit()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup/suggest_limit');
    }

    /**
     * @return mixed|string
     */
    public function getBtnTextColor()
    {
        $color = $this->getValueConfig('ajaxmuntiplecart/success_popup_design/button_text_color');

        return ($color == '') ? '' : $color;
    }

    /**
     * @return mixed
     */
    public function getBtnContinueText()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup_design/continue_text');
    }

    /**
     * @return mixed|string
     */
    public function getBtnContinueBackground()
    {
        $backGround = $this->getValueConfig('ajaxmuntiplecart/success_popup_design/continue');

        return ($backGround == '') ? '' : $backGround;
    }

    /**
     * @return mixed|string
     */
    public function getBtnContinueHover()
    {
        $hover = $this->getValueConfig('ajaxmuntiplecart/success_popup_design/continue_hover');

        return ($hover == '') ? '' : $hover;
    }

    /**
     * @return mixed
     */
    public function getBtnViewcartText()
    {
        return $this->getValueConfig('ajaxmuntiplecart/success_popup_design/viewcart_text');
    }

    /**
     * @return mixed|string
     */
    public function getBtnViewcartBackground()
    {
        $backGround = $this->getValueConfig('ajaxmuntiplecart/success_popup_design/viewcart');

        return ($backGround == '') ? '' : $backGround;
    }

    /**
     * @return mixed|string
     */
    public function getBtnViewcartHover()
    {
        $hover = $this->getValueConfig('ajaxmuntiplecart/success_popup_design/viewcart_hover');

        return ($hover == '') ? '' : $hover;
    }

    /**
     * @return mixed|string
     */
    public function getTextbuttonaddmt()
    {
        $button_text_addmt = $this->getValueConfig('ajaxmuntiplecart/success_popup_design/button_text_addmt');

        return ($button_text_addmt == '') ? '' : $button_text_addmt;
    }

    /**
     * Get config forbid category pages
     *
     * @return null|string
     */
    public function getConfigForbidCategoryPages()
    {
        return $this->getValueConfig(self::PATH_XML_FORBID_CATEGORY_PAGES);
    }

    /**
     * @param $product
     * @param $imageId
     * @param $size
     * @return Image
     */
    public function resizeImage($product, $imageId, $size)
    {
        return $this->productImageHelper
            ->init($product, $imageId)
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepTransparency(true)
            ->keepFrame(false)
            ->resize($size, $size);
    }

    /**
     * @param $store
     * @param $taxClassId
     * @return float
     */
    public function getPercent($store, $taxClassId)
    {
        $taxCalculation = $this->calculationFactory->create();
        $request = $taxCalculation->getRateRequest(null, null, null, $store);
        return $taxCalculation->getRate($request->setProductClassId($taxClassId));
    }

    /**
     * @param $product
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function taxRate($product)
    {
        // old code
        //$store = $this->storeManager->getStore();
        //$percent = $this->getPercent($store, $taxClassId);
        $taxClassId = $product->getTaxClassId();
        $taxPercent = $this->taxCalculation->getCalculatedRate(
            $taxClassId,
            $this->customerSession->getCustomerId(),
            $this->getCurrentStoreId()
        );
        return ($taxPercent / 100);
    }

    /**
     * Get current storeid
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Is mageplaza ajax enabled
     *
     * @return bool
     */
    public function isMageplazaAjaxEnabled()
    {
        return $this->getValueConfig(self::MAGEPLAZA_AJAX_LAYER_ENABLE_CONFIG_XML_PATH);
    }

    /**
     * Is enable for other page of quote_extension
     *
     * @return bool
     */
    public function isEnableOtherPageQuoteExtension()
    {
        try {
            $storeId = $this->getCurrentStoreId();
            $configEnableOtherPage = $this->scopeConfig->isSetFlag(
                self::PATH_REQUEST4QUOTE_ENABLED_OTHER_PAGE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $configEnableQuoteExtension = $this->scopeConfig->isSetFlag(
                self::PATH_REQUEST4QUOTE_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            return $configEnableQuoteExtension && $configEnableOtherPage;
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
            return false;
        }
    }
}
