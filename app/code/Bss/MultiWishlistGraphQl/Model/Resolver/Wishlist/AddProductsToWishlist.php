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
 * @package    Bss_MultiWishlistGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlistGraphQl\Model\Resolver\Wishlist;

use Bss\MultiWishlistGraphQl\Helper\Data as HelperGraphQl;
use Bss\MultiWishlist\Model\ItemOption;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Wishlist;

/**
 * Adding products to wishlist resolver
 */
class AddProductsToWishlist implements ResolverInterface
{
    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @var HelperGraphQl
     */
    protected $helperGraphQl;

    /**
     * @var \Bss\Multiwishlist\Model\ItemOption
     */
    protected $itemOption;

    /**
     * AddProductsToWishlist construct
     *
     * @param HelperGraphQl $helperGraphQl
     * @param \Bss\Multiwishlist\Model\ItemOption $itemOption
     */
    public function __construct(
        HelperGraphQl $helperGraphQl,
        ItemOption $itemOption
    ) {
        $this->helperGraphQl = $helperGraphQl;
        $this->itemOption = $itemOption;
    }

    /**
     * Add products to wishlist
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $wishlist = $this->helperGraphQl->validateInput($context, $args);
        $wishlistItemsCore = $this->itemOption->getWishlistItems($args["wishlistItems"]);
        $wishlistItems = $args["wishlistItems"];
        $result = $this->itemOption->addProductstoMultiWishlists($wishlistItems, $wishlistItemsCore, $wishlist);
        return $this->helperGraphQl->returnResult($result);
    }
}
