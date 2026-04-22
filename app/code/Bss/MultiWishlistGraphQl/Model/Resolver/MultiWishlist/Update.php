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
namespace Bss\MultiWishlistGraphQl\Model\Resolver\MultiWishlist;

use Bss\MultiWishlist\Api\MultiwishlistRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Update bss multi wishlist
 */
class Update implements ResolverInterface
{
    /**
     * @var MultiwishlistRepositoryInterface
     */
    protected $multiWishlistRepository;

    /**
     * Create constructor.
     *
     * @param MultiwishlistRepositoryInterface $multiWishlistRepository
     */
    public function __construct(
        MultiwishlistRepositoryInterface $multiWishlistRepository
    ) {
        $this->multiWishlistRepository = $multiWishlistRepository;
    }

    /**
     * Update wishlist_name of bss wishlist
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
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
        $customerId = $context->getUserId();

        /* Guest checking */
        if (null === $customerId || 0 === $customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }
        if (!isset($args["multi_wishlist_id"])) {
            throw new GraphQlAuthorizationException(
                __("multi_wishlist_id should be specified")
            );
        }
        if (!isset($args["wishlist_name"])) {
            throw new GraphQlAuthorizationException(
                __("wishlist_name should be specified")
            );
        }

        $wishlist = $this->multiWishlistRepository->getById($args["multi_wishlist_id"]);
        $wishlist->setWishlistName($args["wishlist_name"]);
        $this->multiWishlistRepository->save($wishlist);
        return [
            "bssMultiWishlist" => $wishlist,
            "status" => true,
            "message" => __("You update bss multi wishlist success")
        ];
    }
}
