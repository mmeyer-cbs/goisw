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
namespace Bss\QuoteExtension\ViewModel;

use Bss\QuoteExtension\Api\QuoteItemRepositoryInterface;

class Quote implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Bss\QuoteExtension\Model\QuoteEmail
     */
    protected $quoteEmail;

    /**
     * @var QuoteItemRepositoryInterface
     */
    protected $quoteItemRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Bss\QuoteExtension\Model\QuoteEmail $quoteEmail,
        QuoteItemRepositoryInterface $quoteItemRepository
    ) {
        $this->quoteEmail = $quoteEmail;
        $this->quoteItemRepository = $quoteItemRepository;
    }

    /**
     * Get quote by id
     *
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuoteById($quoteId)
   {
      return $this->quoteEmail->getQuoteById($quoteId);
   }

    /**
     *
     * @param int|string $itemId
     * @return string
     */
   public function getQEComment($itemId)
   {
       $searchResult =  $this->quoteItemRepository->getByItemId($itemId);
       foreach ($searchResult->getItems() as $item) {
           return $item->getComment();
       }
       return '';
   }
}
