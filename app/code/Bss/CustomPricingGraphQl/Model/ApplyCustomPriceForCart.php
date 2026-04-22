<?php
declare(strict_types=1);
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
 * @package    Bss_CustomPricingGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricingGraphQl\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Bss\CustomPricingGraphQl\Model\CustomPrice\Applier\PriceApplierInterface;

/**
 * Class ApplyCustomPriceForCart
 * Apply custom pricing for cart
 */
class ApplyCustomPriceForCart
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    private $customerRule;

    /**
     * @var PriceApplierInterface[]
     */
    private $applierPool;

    /**
     * ApplyCustomPriceForCart constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param \Bss\CustomPricing\Helper\CustomerRule $customerRule
     * @param PriceApplierInterface[] $applierPool
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        \Bss\CustomPricing\Helper\CustomerRule $customerRule,
        array $applierPool = []
    ) {
        $this->customerRule = $customerRule;
        $this->cartRepository = $cartRepository;
        $this->applierPool = $applierPool;
    }

    /**
     * Execute apply custom price for cart with list applier pool
     *
     * @param CartInterface|int $cart
     * @throws NoSuchEntityException
     */
    public function execute($cart): CartInterface
    {
        if (is_int($cart)) {
            $cart = $this->cartRepository->get($cart);
        }

        $customer = $cart->getCustomer();
        if ($customer && $customer->getId()) {
            $customerId = $customer->getId();
            $ruleIds = $this->customerRule->getSpecialRuleByCustomerId($customerId);
        } else {
            $ruleIds = $this->customerRule->getSpecialRuleNotLoggedIn();
        }
        $customerGroupId = $cart->getCustomerGroupId();

        if (!$ruleIds) {
            return $cart;
        }

        $ruleIds = explode("-", $ruleIds);

        /** @var Item $item */
        foreach ($cart->getAllItems() as $item) {
            foreach ($this->applierPool as $applier) {
                $applier->apply($item, (int) $customerGroupId, $ruleIds);
            }
        }

        $this->cartRepository->save($cart);
        return $this->cartRepository->get($cart->getId());
    }
}
