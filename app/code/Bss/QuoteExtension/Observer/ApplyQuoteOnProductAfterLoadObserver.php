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
namespace Bss\QuoteExtension\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ApplyQuoteOnProductAfterLoadObserver implements ObserverInterface
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\QuoteExtension\Helper\Admin\ConfigShow
     */
    protected $helperConfig;

    /**
     * AddtoQuoteButton constructor.
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $helper,
        \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig
    ) {
        $this->helper = $helper;
        $this->helperConfig = $helperConfig;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($this->helper->isEnable() && $this->helper->isActiveRequest4Quote($product)) {
            $product->setIsActiveRequest4Quote(true);
            $isEnableProductPage = $this->helperConfig->isEnableProductPage();
            if ($isEnableProductPage) {
                $product->setIsActiveRequest4QuoteProductPage(true);
            }
        }
        return $this;
    }
}
