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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\QuoteExtension\Item;

/**
 * Quote Item Configure block
 * Updates templates and blocks to show 'Update Cart' button and set right form submit url
 */
class Configure extends \Magento\Framework\View\Element\Template
{
    /**
     * Configure product view blocks
     * @return \Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        // Set custom submit url route for form - to submit updated options to cart
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $itemId = (int) $this->getRequest()->getParam('id');
            if ($itemId) {
                $block->setSubmitRouteData(
                    [
                        'route' => 'quoteextension/quote/updateItemOptions',
                        'params' => ['id' => $itemId],
                    ]
                );
            }
        }

        return parent::_prepareLayout();
    }
}
