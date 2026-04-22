<?php
declare(strict_types=1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\ViewModel;

/**
 * Class RegistryData
 *
 * @package Bss\CompanyAccount\ViewModel
 */
class RegistryData implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Shipping\Helper\Data
     */
    private $shippingHelper;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * RegistryData constructor.
     *
     * @param \Magento\Shipping\Helper\Data $shippingHelper
     * @param \Magento\Tax\Helper\Data $taxHelper
     */
    public function __construct(
        \Magento\Shipping\Helper\Data $shippingHelper,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        $this->shippingHelper = $shippingHelper;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Get shipping helper
     *
     * @return \Magento\Shipping\Helper\Data
     */
    public function getShippingHelper()
    {
        return $this->shippingHelper;
    }

    /**
     * Get tax helper object
     *
     * @return \Magento\Tax\Helper\Data
     */
    public function getTaxHelper()
    {
        return $this->taxHelper;
    }
}
