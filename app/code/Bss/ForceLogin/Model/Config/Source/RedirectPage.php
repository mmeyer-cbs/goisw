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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Model\Config\Source;

class RedirectPage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get Redirect Url
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'customer/account/index', 'label' => __('Default')],
            ['value' => 'home', 'label' => __('Home Page')],
            ['value' => 'previous', 'label' => __('Previous Url')],
            ['value' => 'customurl', 'label' => __('Custom Url')],
        ];
    }
}
