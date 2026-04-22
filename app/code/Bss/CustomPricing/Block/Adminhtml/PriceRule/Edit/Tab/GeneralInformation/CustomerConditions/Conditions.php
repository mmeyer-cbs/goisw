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

namespace Bss\CustomPricing\Block\Adminhtml\PriceRule\Edit\Tab\GeneralInformation\CustomerConditions;

/**
 * Class Conditions render conditions
 */
class Conditions implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Render html for customer conditions
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        try {
            if ($element->getRule() && $element->getRule()->getCustomerConditions()) {
                return $element->getRule()->getCustomerConditions()->asHtmlRecursive();
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
