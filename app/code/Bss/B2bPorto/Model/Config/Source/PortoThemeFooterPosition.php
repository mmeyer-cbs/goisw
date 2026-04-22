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
 * @package     BSS_B2bPorto
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\B2bPorto\Model\Config\Source;

/**
 * Class PortoThemeFooterPosition
 *
 * @package Bss\B2bPorto\Model\Config\Source
 */
class PortoThemeFooterPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'footer_middle', 'label' => __('Footer Middle 1')],
            ['value' => 'footer_middle_2', 'label' => __('Footer Middle 2')]
        ];
    }
}
