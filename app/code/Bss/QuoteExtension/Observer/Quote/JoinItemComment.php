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
namespace Bss\QuoteExtension\Observer\Quote;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class JoinItemComment
 *
 * @package Bss\QuoteExtension\Observer\Quote
 */
class JoinItemComment implements ObserverInterface
{
    /**
     * Join Item Comment
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getCollection();

        if ($collection instanceof \Magento\Quote\Model\ResourceModel\Quote\Item\Collection) {
            $quoteItemTable = $collection->getTable('quote_extension_item');
            $collection->getSelect()->joinLeft(
                $quoteItemTable,
                sprintf(
                    '%s.item_id = main_table.item_id',
                    $quoteItemTable
                ),
                sprintf('%s.comment AS current_comment_item_id', $quoteItemTable)
            );
        }
    }
}
