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

use Bss\MultiWishlist\Api\MultiwishlistRepositoryInterface;
use Bss\Multiwishlist\Model\ResourceModel\WishlistLabel\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Class WishlistLabelRepository
 *
 * @package Bss\MultiWishlist\Model
 */
class WishlistLabelRepository implements MultiwishlistRepositoryInterface
{
    /**
     * Error message codes
     */
    protected const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    protected const ERROR_SAVE = 'NOT_SAVE';
    protected const ERROR_DELETE = 'NOT_DELETE';
    protected const ERROR_UNDEFINED = 'UNDEFINED';
    protected const ERROR_UNDEFINED_ITEM = 'UNDEFINED';

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    protected $wishlistCollection;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * @var ResourceModel\WishlistLabel
     */
    protected $resource;

    /**
     * @var WishlistLabelFactory
     */
    protected $wishlistLabelFactory;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var ItemOption
     */
    protected $itemOption;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Wishlist
     */
    protected $wishlist;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    protected $userContext;

    /**
     * WishlistLabelRepository constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     * @param CollectionFactory $wishlistCollection
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\WishlistLabel $resource
     * @param WishlistLabelFactory $wishlistLabelFactory
     * @param WishlistFactory $wishlistFactory
     * @param ItemOption $itemOption
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Wishlist\Model\ResourceModel\Wishlist $wishlist
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder                           $criteriaBuilder,
        CollectionProcessor                                                    $collectionProcessor,
        \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\CollectionFactory $wishlistCollection,
        SearchResultsInterfaceFactory                                          $searchResultsFactory,
        ResourceModel\WishlistLabel                                            $resource,
        WishlistLabelFactory                                                   $wishlistLabelFactory,
        WishlistFactory                                                        $wishlistFactory,
        ItemOption                                                             $itemOption,
        \Magento\Wishlist\Model\ItemFactory                                    $itemFactory,
        \Magento\Catalog\Model\ProductRepository                               $productRepository,
        \Magento\Framework\App\ResourceConnection                              $resourceConnection,
        \Magento\Wishlist\Model\ResourceModel\Wishlist                         $wishlist,
        \Magento\Authorization\Model\CompositeUserContext                      $userContext
    ) {
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->wishlistCollection = $wishlistCollection;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->resource = $resource;
        $this->wishlistLabelFactory = $wishlistLabelFactory;
        $this->wishlistFactory = $wishlistFactory;
        $this->itemOption = $itemOption;
        $this->itemFactory = $itemFactory;
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
        $this->wishlist = $wishlist;
        $this->userContext = $userContext;
    }

    /**
     * @inheritDoc
     */
    public function getById($wishlistId)
    {
        if (!isset($this->instances[$wishlistId])) {
            $mWishlist = $this->wishlistLabelFactory->create();
            $this->resource->load($mWishlist, $wishlistId);
            if (!$mWishlist->getId()) {
                throw new NoSuchEntityException(__('Wish list with id "%1" does not exist.', $wishlistId));
            }
            $this->instances[$wishlistId] = $mWishlist;
        }
        return $this->instances[$wishlistId];
    }

    /**
     * Get Wishlist Label
     *
     * @param int $wishlistId
     * @param int $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function get($wishlistId, $storeId = null)
    {
        $cacheKey = 'all';
        if ($storeId) {
            $cacheKey = $storeId;
        }
        if (!isset($this->instances[$wishlistId][$cacheKey])) {
            $mWishlist = $this->wishlistLabelFactory->create();

            $this->resource->load($mWishlist, $wishlistId);
            if (!$mWishlist->getId()) {
                throw NoSuchEntityException::singleField('id', $wishlistId);
            }
            $this->instances[$wishlistId][$cacheKey] = $mWishlist;
        }
        return $this->instances[$wishlistId][$cacheKey];
    }

    /**
     * Save bss multi wishlist
     *
     * @param \Bss\MultiWishlist\Api\Data\MultiwishlistInterface $multiwishlist
     * @return \Bss\MultiWishlist\Api\Data\MultiwishlistInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save($multiwishlist)
    {
        try {
            $validateExistName = $this->validateExistName($multiwishlist->getWishlistName());
            if (!$validateExistName) {
                $this->resource->save($multiwishlist);
                return $multiwishlist;
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save wishlist: %1',
                    $exception->getMessage()
                )
            );
        }
        throw new CouldNotSaveException(
            __($validateExistName)
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteByMultiWishlistId($multiWishlistId)
    {
        try {
            $wishlist = $this->getById($multiWishlistId);
            $this->resource->delete($wishlist);
            $result["status"] = [
                "success" => true,
                "message" => __("You deleted.")
            ];

        } catch (\Exception $exception) {
            $result["status"] = [
                "success" => false,
                "message" => __($exception->getMessage())
            ];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function validateExistName($wishlistName)
    {
        if (!$wishlistName) {
            return __("wishlist name should be specified");
        }
        $searchCriteria = $this->criteriaBuilder->addFilter("wishlist_name", $wishlistName)
            ->create();
        $wishlistCollection = $this->getList($searchCriteria);
        if ($wishlistCollection->getTotalCount() || strtolower($wishlistName) == 'main') {
            return __('Already exist a Wishlist. Please choose a different name.');
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->wishlistCollection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function getListByCustomerId($customerId)
    {
        $searchCriteria = $this->criteriaBuilder->addFilter("customer_id", $customerId)
            ->create();
        return $this->getList($searchCriteria)->getItems();
    }

    /**
     * @inheritDoc
     */
    public function addProductsToWishList($wishListId, $wishlistItems)
    {
        $wishlistItemsCore = $this->itemOption->getWishlistItems($wishlistItems);
        $wishlist = $this->wishlistFactory->create()->load($wishListId);
        $result = $this->itemOption->addProductstoMultiWishlists($wishlistItems, $wishlistItemsCore, $wishlist);
        return $result['errors'];
    }

    /**
     * @inheritDoc
     */
    public function deleteProductsOfWishList($customerId, $wishlistItemIds)
    {
        $errors = [];
        try {
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
            $items = $wishlist->getItemCollection();
            foreach ($wishlistItemIds as $item) {
                try {
                    if ($itemCheck = $this->compareItemIds($item, $items)) {
                        $itemCheck->delete();
                        $wishlist->save();
                        continue;
                    }
                    $errors[] = [
                        "message" => __(
                            'The item with id "%id" does not exist in wishlist',
                            ['id' => $item]
                        )->render(),
                        "code" => self::ERROR_UNDEFINED_ITEM
                    ];
                } catch (\Exception $exception) {
                    $errors[] = [
                        "message" => __(
                            'Could not delete the item with id "%id" to the wishlist:: %message',
                            ['id' => $item, 'message' => $exception]
                        )->render(),
                        "code" => self::ERROR_DELETE
                    ];
                    continue;
                }
            }
        } catch (\Exception $exception) {
            $errors[] = [
                "message" => __(
                    'Could not get the wishlist by customer with id "%id": %message',
                    ['id' => $customerId, 'message' => $exception]
                )->render(),
                "code" => self::ERROR_UNDEFINED
            ];
        }
        if ($errors) {
            return $errors;
        }
        return __('Success!')->render();
    }

    /**
     * Compare 2 item id
     *
     * @param string $needle
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Collection $wishlistItems
     * @return false|\Magento\Wishlist\Model\Item
     */
    protected function compareItemIds($needle, $wishlistItems)
    {
        foreach ($wishlistItems as $item) {
            if ($needle == $item->getId()) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getListProductByWishListAndCustomerId($customerId, $multiWishListId)
    {
        try {
            $itemCollection = $this->prepareCollection($customerId, $multiWishListId);
            if ($multiWishListId != 0) {
                $result['wishlist'] = $this->get($multiWishListId)->getData();
            } else {
                $result['wishlist'] = [
                    'multi_wishlist_id' => 0,
                    'customer_id' => $customerId,
                    'wishlist_name' => 'Main'
                ];
            }
            foreach ($itemCollection as $item)
            {
                $product = $this->productRepository->getById($item['product_id']);
                $result['wishlist']['item'][] = $product->getData();
            }
            return $result;
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(
                __('Some errors when getList product in wishList "%2"', $exception->getMessage())
            );
        }
    }

    /**
     * Prepare item collection wishlist
     *
     * @param int $customerId
     * @param int $multiWishListId
     * @return \Magento\Framework\DataObject[]|\Magento\Wishlist\Model\Item[]
     */
    public function prepareCollection($customerId, $multiWishListId)
    {
        $itemCollection = $this->itemFactory->create()->getCollection();
        $itemCollection->getSelect()->joinLeft(
            [
                "wishlist" => $this->resourceConnection->getConnection()->getTableName('wishlist')
            ],
            'main_table.wishlist_id = wishlist.wishlist_id',
            [
                'customer_id' => 'wishlist.customer_id',
            ]
        )->where('main_table.multi_wishlist_id = ?', $multiWishListId)
            ->where("wishlist.customer_id = ?", $customerId);
        return $itemCollection->getData();
    }


    /**
     * @inheritDoc
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getListProductUserToken($customerId = null)
    {
        if (!$customerId){
            $customerId = $this->userContext->getUserId();
        }
        $wishlist = $this->wishlistFactory->create();
        $this->wishlist->load($wishlist, $customerId, 'customer_id');
        if (null === $wishlist->getId()) {
            return [];
        }
        $result['wishlist'] = $wishlist->getData();
        foreach ($wishlist->getItemCollection()->getItems() as $item)
        {
            $product = $this->productRepository->getById($item['product_id']);
            $result['wishlist']['item'][] = $product->getData();
        }
        return $result;
    }
}
