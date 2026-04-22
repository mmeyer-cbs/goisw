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
namespace Bss\MultiWishlistGraphQl\Helper;

use Bss\MultiWishlist\Helper\Data as Helper;
use Bss\MultiWishlist\Model\ItemOption;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item as WishlistItemResource;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\BuyRequest\BuyRequestBuilder;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;

/**
 * Class Data
 *
 * @package Bss\MultiWishlist\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    /**
     * @var ItemFactory
     */
    protected $itemFactory;
    /**
     * @var WishlistItemResource
     */
    protected $wishlistItemResource;

    /**
     * @var BuyRequestBuilder
     */
    protected $buyRequestBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Helper
     */
    protected $helperData;

    /**
     * @var WishlistDataMapper
     */
    protected $wishlistDataMapper;

    /**
     * @var WishlistResourceModel
     */
    protected $wishlistResource;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var ItemOption
     */
    protected $itemOption;

    /**
     * Data constructor.
     *
     * @param ItemFactory $itemFactory
     * @param WishlistItemResource $wishlistItemResource
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Helper $helperData
     * @param WishlistResourceModel $wishlistResource
     * @param WishlistFactory $wishlistFactory
     * @param WishlistDataMapper $wishlistDataMapper
     * @param Context $context
     * @param ItemOption $optionHelper
     */
    public function __construct(
        ItemFactory $itemFactory,
        WishlistItemResource $wishlistItemResource,
        BuyRequestBuilder $buyRequestBuilder,
        \Magento\Framework\App\RequestInterface $request,
        Helper $helperData,
        WishlistResourceModel $wishlistResource,
        WishlistFactory $wishlistFactory,
        WishlistDataMapper $wishlistDataMapper,
        Context $context,
        ItemOption $itemOption
    ) {
        $this->itemFactory = $itemFactory;
        $this->wishlistItemResource = $wishlistItemResource;
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->request = $request;
        $this->helperData = $helperData;
        $this->wishlistDataMapper = $wishlistDataMapper;
        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
        $this->itemOption = $itemOption;
        parent::__construct($context);
    }

    /**
     * Get customer wishlist
     *
     * @param int|null $wishlistId
     * @param int|null $customerId
     *
     * @return Wishlist
     */
    public function getWishlist($wishlistId, $customerId)
    {
        $wishlist = $this->wishlistFactory->create();

        if ($wishlistId !== null && $wishlistId > 0) {
            $this->wishlistResource->load($wishlist, $wishlistId);
        } elseif ($customerId !== null) {
            $wishlist->loadByCustomerId($customerId, true);
        }

        return $wishlist;
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
        return $this->$itemOption->getWishlistItems($wishlistItemsData);
    }

    /**
     * Get wishlist item
     *
     * @param int $wishlistItemId
     * @return \Magento\Wishlist\Model\Item
     */
    public function getWishlistItem($wishlistItemId)
    {
        $wishlistItem = $this->itemFactory->create();
        $this->wishlistItemResource->load($wishlistItem, $wishlistItemId);
        return $wishlistItem;
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
        return $this->$itemOption->buildOptions($wishlistItems, $productId, $wishlistInput);
    }

    /**
     * Return result
     *
     * @param array $result
     * @return array
     */
    public function returnResult($result)
    {
        return [
            'wishlist' => $this->wishlistDataMapper->map($result['wishlist']),
            'user_errors' => $result['errors']
        ];
    }

    /**
     * Write log
     *
     * @param string $message
     */
    public function writeLog($message)
    {
        $this->_logger->critical($message);
    }

    /**
     * Validate input
     *
     * @param ContextInterface $context
     * @param array|null $args
     * @return Wishlist
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateInput($context, $args)
    {
        $customerId = $context->getUserId();

        /* Guest checking */
        if (null === $customerId || 0 === $customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }

        $wishlistId = ((int) $args['wishlistId']) ?: null;
        $wishlist = $this->getWishlist($wishlistId, $customerId);
        if (null === $wishlist->getId() || $customerId !== (int) $wishlist->getCustomerId()) {
            throw new GraphQlInputException(__('The wishlist was not found.'));
        }
        return $wishlist;
    }
}
