<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\MultiWishlist\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Wishlist\BuyRequest\BuyRequestBuilder;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItemFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Option
 *
 * @package Bss\MultiWishlist\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemOption
{
    /**
     * Error message codes
     */
    protected const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    protected const ERROR_SAVE = 'NOT_SAVE';
    protected const ERROR_UNDEFINED = 'UNDEFINED';

    /**
     * @var BuyRequestBuilder
     */
    protected $buyRequestBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Option constructor.
     *
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        BuyRequestBuilder                       $buyRequestBuilder,
        \Magento\Framework\App\RequestInterface $request,
        LoggerInterface                         $logger,
        ProductRepositoryInterface              $productRepository
    ) {
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->request = $request;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    /**
     * Build options
     *
     * @param WishlistItem $wishlistItems
     * @param int $productId
     * @param array $wishlistInput
     * @return \Magento\Framework\DataObject
     */
    public function buildOptions($wishlistItems, $productId, $wishlistInput)
    {
        $multiWishlistId = 0;
        $wishlistInput["wishlist_id"] = $multiWishlistId;
        $this->request->setPostValue($wishlistInput);
        return $this->buyRequestBuilder->build($wishlistItems, $productId);
    }

    /**
     * Get wishlist items
     *
     * @param array $wishlistItemsData
     *
     * @return array
     */
    public function getWishlistItems(array $wishlistItemsData): array
    {
        $wishlistItems = [];

        foreach ($wishlistItemsData as $wishlistItemData) {
            $wishlistItems[] = (new WishlistItemFactory())->create($wishlistItemData);
        }
        return $wishlistItems;
    }

    /**
     * @param array $wishlistItems
     * @param array $wishlistItemsCore
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @return array
     */
    public function addProductstoMultiWishlists($wishlistItems, $wishlistItemsCore, $wishlist)
    {
        $errors = [];
        foreach ($wishlistItems as $key => $wishlistInput) {
            try {
                $sku = $wishlistInput["parent_sku"] ?? $wishlistInput["sku"];
                $product = $this->productRepository->get($sku, false, null, true);
            } catch (NoSuchEntityException $e) {
                $this->logger->critical($e->getMessage());
                $errors[] = [
                    "message" => __('Could not find a product with SKU "%sku"', ['sku' => $wishlistInput["sku"]])->render(),
                    "code" => self::ERROR_PRODUCT_NOT_FOUND
                ];
                continue;
            }
            $options = $this->buildOptions(
                $wishlistItemsCore[$key],
                (int)$product->getId(),
                $wishlistInput
            );
            try {
                $result = $wishlist->addNewItem($product, $options, false);
                if (is_string($result)) {
                    $errors[] = [
                        "message" => __("%1", $result
                        )->render(),
                        "code" => self::ERROR_UNDEFINED
                    ];
                    continue;
                }
                $result->setMultiWishlistId($wishlistInput['multi_wishlist_id']);
                $wishlist->save();
                $errors[] = [__('Successfully save the item with product with SKU "%sku" to the wishlist.'
                    ,['sku' => $wishlistInput["sku"]])];
            } catch (\Exception $exception) {
                $errors[] = [
                    "message" => __(
                        'Could not add the product with SKU "%sku" to the wishlist:: %message',
                        ['sku' => $wishlistInput["sku"], 'message' => $exception->getMessage()]
                    )->render(),
                    "code" => self::ERROR_SAVE
                ];
            }
        }
        return [
            'wishlist' => $wishlist,
            'errors' => $errors
        ];
    }
}
