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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Paymentshipping\Plugin;

use Magento\Backend\Model\Session\Quote;

class ShippingMethodManagement
{
    /**
     * @var \Bss\Paymentshipping\Helper\Data
     */
    protected $bssHelper;

    /**
     * @var Quote
     */
    protected $backendQuoteSession;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Constructor
     *
     * @param \Bss\Paymentshipping\Helper\Data $bssHelper
     */
    public function __construct(
        \Bss\Paymentshipping\Helper\Data $bssHelper,
        Quote $backendQuoteSession,
        \Magento\Framework\App\State $appState
    ) {
        $this->backendQuoteSession = $backendQuoteSession;
        $this->bssHelper = $bssHelper;
        $this->appState = $appState;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param array $shippingRates
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterGetGroupedAllShippingRates(\Magento\Quote\Model\Quote\Address $subject, $shippingRates)
    {
        $myHelperData = $this->bssHelper;
        foreach ($shippingRates as $methodCode => $method) {
            if ($this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                $websiteId = $this->backendQuoteSession->getQuote()->getStore()->getWebsiteId();
                if (!$myHelperData->canUseMethod($methodCode, 'shipping', $websiteId)) {
                    unset($shippingRates[$methodCode]);
                }
            } else {
                if (!$myHelperData->canUseMethod($methodCode, 'shipping')) {
                    unset($shippingRates[$methodCode]);
                }
            }
        }
        return $shippingRates;
    }
}
