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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Wishlist;

/**
 * Adding products to wishlist resolver
 */
class UpdateProductsInWishlist implements ResolverInterface
{
    /**
     * Error message codes
     */
    private const ERROR_SAVE = 'NOT_SAVE';
    private const ITEM_ID_NOT_FOUND = 'ITEM_ID_NOT_FOUND';
    private const ERROR_UNDEFINED = 'UNDEFINED';

    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @var HelperGraphQl
     */
    protected $helperGraphQl;

    /**
     * AddProductsToWishlist construct
     *
     * @param HelperGraphQl $helperGraphQl
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        HelperGraphQl $helperGraphQl,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helperGraphQl = $helperGraphQl;
        $this->productRepository = $productRepository;
    }

    /**
     * Update wishlist item
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
        $errors = [];
        $wishlistItemsCore = $this->helperGraphQl->getWishlistItems($args["wishlistItems"]);

        $wishlistItems = $args["wishlistItems"];
        foreach ($wishlistItems as $key => $wishlistInput) {
            $wishlistItem = $this->helperGraphQl->getWishlistItem($wishlistInput["wishlist_item_id"]);
            if (!$wishlistItem->getId()) {
                $errors[] = [
                    "message" => __("Not found wishlist_item_id: %1", $wishlistInput["wishlist_item_id"]
                    )->render(),
                    "code" => self::ITEM_ID_NOT_FOUND
                ];
                continue;
            }
            $wishlistInput["multi_wishlist_id"] = $wishlistItem->getMultiWishlistId();
            $options = $this->helperGraphQl->buildOptions($wishlistItemsCore[$key], (int) $wishlistItem->getProductId(), $wishlistInput);
            if (isset($wishlistInput["description"])) {
                $options->setDescription($wishlistInput["description"]);
            } else {
                $options->setDescription($wishlistItem->getDescription());
            }
            try {
                $result = $wishlist->updateItem($wishlistInput["wishlist_item_id"], $options);
                if (is_string($result)) {
                    $errors[] = [
                        "message" => __("%1", $result
                        )->render(),
                        "code" => self::ERROR_UNDEFINED
                    ];
                    continue;
                }
                $wishlist->save();
            } catch (\Exception $exception) {
                $errors[] = [
                    "message" => __(
                        'Could not update wishlist item with wishlist_item_id "%wishlist_item_id" to the wishlist:: %message',
                        ['wishlist_item_id' => $wishlistInput["wishlist_item_id"], 'message' => $exception->getMessage()]
                    )->render(),
                    "code" => self::ERROR_SAVE
                ];
            }

        }

        return $this->helperGraphQl->returnResult($wishlist, $errors);
    }
}
