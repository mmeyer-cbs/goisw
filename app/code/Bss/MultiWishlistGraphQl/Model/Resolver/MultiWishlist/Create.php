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
 * Create bss multi wishlist
 */
class Create implements ResolverInterface
{
    /**
     * @var MultiwishlistRepositoryInterface
     */
    protected $multiWishlistRepository;

    /**
     * @var \Bss\MultiWishlist\Api\Data\MultiwishlistInterface
     */
    protected $multiwishlist;

    /**
     * Create constructor.
     *
     * @param \Bss\MultiWishlist\Api\Data\MultiwishlistInterface $multiwishlist
     * @param MultiwishlistRepositoryInterface $multiWishlistRepository
     */
    public function __construct(
        \Bss\MultiWishlist\Api\Data\MultiwishlistInterface $multiwishlist,
        MultiwishlistRepositoryInterface $multiWishlistRepository
    ) {
        $this->multiwishlist = $multiwishlist;
        $this->multiWishlistRepository = $multiWishlistRepository;
    }

    /**
     * Add products to wishlist
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
        if (!isset($args["wishlist_name"])) {
            throw new GraphQlAuthorizationException(
                __("wishlist_name should be specified")
            );
        }
        $this->multiwishlist->setWishlistName($args["wishlist_name"]);
        $this->multiwishlist->setCustomerId($customerId);
        try {
            $wishlist = $this->multiWishlistRepository->save($this->multiwishlist);
            return [
                "bssMultiWishlist" => $wishlist,
                "status" => true,
                "message" => __("You create bss multi wishlist success")
            ];
        } catch (\Exception $exception) {
            return [
                "message" => $exception->getMessage(),
                "status" => false
            ];
        }
    }
}
