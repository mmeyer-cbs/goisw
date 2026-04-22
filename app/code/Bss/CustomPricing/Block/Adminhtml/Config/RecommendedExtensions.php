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

namespace Bss\CustomPricing\Block\Adminhtml\Config;

/**
 * Class RecommendedExtensions
 */
//@codingStandardsIgnoreLine
class RecommendedExtensions extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Bss\CustomPricing\Helper\Integrate
     */
    private $integrate;

    /**
     * RecommendedExtensions constructor.
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Bss\CustomPricing\Helper\Integrate $integrate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Bss\CustomPricing\Helper\Integrate $integrate,
        array $data = []
    ) {
        $this->integrate = $integrate;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Get defined bss modules data
     *
     * @return array
     */
    protected function getIntegrateExtensions()
    {
        //@codingStandardsIgnoreStart
        return [
            [
                'name' => __('BSS Customer Attribute'),
                'status' => $this->integrate->isModuleBssCustomerAttributeInstalled(),
                'description' => __(
                    'Select customer to price rule by condition<br /> %1',
                    "<a style='color:#006bb4' target='_blank' href='https://bsscommerce.com/magento-2-customer-attributes-extension.html'>Check now!</a>"
                )
            ],
            [
                'name' => __('BSS Hide Price'),
                'status' => $this->integrate->isModuleBssHidePriceInstalled(),
                'description' => __(
                    "Hide the custom price<br> %1",
                    "<a style='color:#006bb4' target='_blank' href='https://bsscommerce.com/magento-2-hide-price-extension.html'>Check now!</a>"
                )
            ]
        ];
        //@codingStandardsIgnoreEnd
    }

    /**
     * Return list of Integrated Bss Module and installed status
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed|string
     */
    public function _getHeaderHtml($element)
    {
        $headerHtml = parent::_getHeaderHtml($element);

        $rowExt = '';
        foreach ($this->getIntegrateExtensions() as $extension) {
            $statusValueText = $extension['status'] ? "<b style='color:green'>" . __('Installed') . "</b>" :
                "<b style='color:red'>" . __('Not Installed') . "</b>";
            $statusText = __('Status');
            $rowExt .=
                "<tr id='row_tax_display_type'>
                    <td class='label'><label for='tax_display_type'>{$extension['name']}</span></label></td>
                    <td class='value'>
                        $statusText: $statusValueText
                        <p class='note'><span>{$extension['description']}</span></p>
                    </td>
                </tr>";
        }

        $headerHtml = str_replace(
            '<table cellspacing="0" class="form-list">',
            '<table cellspacing="0" class="form-list">' . "<tbody>$rowExt</tbody>",
            $headerHtml
        );

        return $headerHtml;
    }
}
