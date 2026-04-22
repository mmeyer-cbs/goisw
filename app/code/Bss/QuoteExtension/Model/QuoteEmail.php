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
namespace Bss\QuoteExtension\Model;

class QuoteEmail
{
    /**
     * @var \Magento\Quote\Api\Data\CartInterface|null
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->quoteRepository = $cartRepository;
    }

    /**
     * Get quote by id
     *
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuoteById($quoteId)
    {
        if (!$this->quote && $quoteId) {
            try {
                $quote =  $this->quoteRepository->get($quoteId);
            } catch (\Exception $exception) {
                $quote = null;
            }
            $this->quote = $quote;
        }
        return $this->quote;
    }
}
