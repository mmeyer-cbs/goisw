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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Helper;

/**
 * Class Integrate
 */
class Integrate extends \Magento\Framework\App\Helper\AbstractHelper
{
    //@codingStandardsIgnoreStart
    /**
     * Check if module customer attribute was installed
     *
     * @return bool
     */
    public function isModuleBssCustomerAttributeInstalled()
    {
        return $this->isModuleOutputEnabled('Bss_CustomerAttributes');
    }

    /**
     * Module Hide price was installed
     *
     * @return bool
     */
    public function isModuleBssHidePriceInstalled()
    {
        return $this->isModuleOutputEnabled('Bss_HidePrice');
    }

    //@codingStandardsIgnoreEnd
}
