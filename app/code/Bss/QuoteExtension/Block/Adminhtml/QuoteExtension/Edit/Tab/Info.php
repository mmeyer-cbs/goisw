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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Tab;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class Info extends AbstractOrder implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Retrieve available order
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote()
    {
        return $this->_coreRegistry->registry('quoteextension_quote');
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getMageQuote()
    {
        return $this->_coreRegistry->registry('mage_quote');
    }

    /**
     * Retrieve source model instance
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSource()
    {
        return $this->getQuote();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getQuoteTotalData()
    {
        return [];
    }

    /**
     * Get order info data
     *
     * @return array
     */
    public function getQuoteInfoData()
    {
        return [];
    }

    /**
     * Get tracking html
     *
     * @return string
     */
    public function getTrackingHtml()
    {
        return $this->getChildHtml('order_tracking');
    }

    /**
     * Get items html
     *
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('quote_extension_items');
    }

    /**
     * Retrieve gift options container block html
     *
     * @return string
     */
    public function getGiftOptionsHtml()
    {
        return $this->getChildHtml('gift_options');
    }

    /**
     * Get payment html
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    /**
     * View URL getter
     *
     * @param int $orderId
     * @return string
     */
    public function getViewUrl($orderId)
    {
        return $this->getUrl('bss_quote_extension/*/*', ['quote_id' => $orderId]);
    }

    /**
     * ######################## TAB settings #################################
     */

    /**
        <address class="admin__page-section-item-content">
            <?php echo $block->getFormattedAddress('shipping'); ?>
        </address>
        <?php echo $block->getSetDefaultShippingAddressHtml(); ?>
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * { @inheritdoc }
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Manaquote Information');
    }

    /**
     * { @inheritdoc }
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * { @inheritdoc }
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
