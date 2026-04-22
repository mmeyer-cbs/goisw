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
namespace Bss\QuoteExtension\Plugin\Model\Cart;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\CartTotalRepository as CoreCartTotalRepository;
use Magento\Quote\Model\Cart\Totals;

/**
 * CartTotalRepository calculate total shipping price for multishipping
 */
class CartTotalRepository
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Overwrite the CoreCartTotalRepository quoteTotal and update the shipping price
     *
     * @param CoreCartTotalRepository $subject
     * @param Totals $quoteTotals
     * @param Int $cartId
     * @return Totals
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        CoreCartTotalRepository $subject,
        Totals $quoteTotals,
        $cartId
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->getIsMultiShipping()) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if (isset($shippingMethod) && !empty($shippingMethod)) {
                $shippingRate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                $shippingPrice = $shippingRate->getPrice();
            } else {
                $shippingPrice = $quote->getShippingAddress()->getShippingAmount();
            }
            /**
             * @var \Magento\Store\Api\Data\StoreInterface
             */
            $store = $quote->getStore();
            $amountPrice = $store->getBaseCurrency()
                ->convert($shippingPrice, $store->getCurrentCurrencyCode());
            $quoteTotals->setBaseShippingAmount($shippingPrice);
            $quoteTotals->setShippingAmount($amountPrice);
        }
        return $quoteTotals;
    }
}
