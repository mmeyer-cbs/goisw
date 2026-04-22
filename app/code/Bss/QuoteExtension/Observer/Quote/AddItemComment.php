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

use Bss\QuoteExtension\Model\ResourceModel\QuoteItem;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddItemComment
 * @package Bss\QuoteExtension\Observer\Quote
 */
class AddItemComment implements ObserverInterface
{
    /**
     * @var QuoteItem
     */
    protected $quoteItem;

    /**
     * AddItemComment constructor.
     *
     * @param QuoteItem $quoteItem
     */
    public function __construct(QuoteItem $quoteItem)
    {
        $this->quoteItem = $quoteItem;
    }

    /**
     * Add Item Comment
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $data = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getData('current_comment_item_id')) {
                continue;
            }
            $itemInfo = $this->quoteItem->findItemComment($item->getId());
            if (is_array($itemInfo) && isset($itemInfo['item_id'])) {
                continue;
            } else {
                $data[] = [
                    'item_id' => $item->getId(),
                    'comment'=> $item->getData('current_comment_item_id')
                ];
            }
        }
        if (!empty($data)) {
            $this->quoteItem->insertMultiple($data);
        }
    }
}
