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
namespace Bss\QuoteExtension\Block\Style;

/**
 * Class Config
 *
 * @package Bss\QuoteExtension\Block\Style
 */
class Config extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\QuoteExtension\Helper\Admin\ConfigShow
     */
    protected $helperStyle;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Config constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperStyle
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperStyle,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperStyle = $helperStyle;
        $this->request = $request;
    }

    /**
     * Get Customer Style for Add To Quote Button
     *
     * @return bool|mixed
     */
    public function getCustomStyle()
    {
        $enableProductPage = $this->helperStyle->isEnableProductPage();
        $enableOtherPage = $this->helperStyle->isEnableOtherPage();
        $page = $this->request->getFullActionName();
        if ($page == "catalog_product_view" && $enableProductPage) {
            return $this->helperStyle->getProductPageCustomStyle();
        } elseif ($enableOtherPage) {
            return $this->helperStyle->getOtherPageCustomStyle();
        }
        return false;
    }
}
