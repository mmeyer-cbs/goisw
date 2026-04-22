<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     Bss_ReorderProduct
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ReorderProduct\ViewModel;

use Bss\ReorderProduct\Helper\Data;

/**
 * Class RegistryData
 *
 * @package Bss\ReorderProduct\ViewModel
 */
class RegistryData implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * RegistryData constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get module helper
     *
     * @return Data
     */
    public function getModuleHelper()
    {
        return $this->helper;
    }
}
