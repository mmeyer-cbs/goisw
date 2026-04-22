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
 * @package    Bss_ReorderProductGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ReorderProductGraphQl\Model\Resolver;

use Bss\ReorderProduct\Block\ReorderProduct;
use Bss\ReorderProduct\Helper\Data;
use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;

/**
 * class ListProductReorder
 *
 * @package Bss\ReorderProductGraphQl\Model\Resolver\
 */
class ListProductReorder implements ResolverInterface
{
    /**
     * @var ReorderProduct
     */
    protected $reorderProduct;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Currency
     */
    protected $currency;
    /**
     * @var Data
     */
    protected $data;

    /**
     * Constructor
     *
     * @param ReorderProduct $reorderProduct
     * @param StoreManagerInterface $storeManager
     * @param Currency $currency
     * @param Data $data
     */
    public function __construct(
        ReorderProduct        $reorderProduct,
        StoreManagerInterface $storeManager,
        Currency              $currency,
        Data                  $data
    ) {
        $this->reorderProduct = $reorderProduct;
        $this->storeManager = $storeManager;
        $this->currency = $currency;
        $this->data = $data;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * InitializeData Page Size
     *
     * @param $args
     * @return void
     */
    public function initializeData(&$args)
    {
        if (!isset($args['pageSize'])) {
            $args['pageSize'] = (int)$this->data->getListperpage();
        }
    }

    /**
     * Validate input
     *
     * @param $args
     * @return void
     * @throws GraphQlInputException
     */
    public function validate($args)
    {
        if ($args['pageSize'] < 1 || $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('Page Size and Current Page value must be greater than 0.'));
        }
    }
    /**
     * Avaiable orders config array
     *
     * @return array
     */
    public function getAvailableOrders()
    {
        $sort = [
            'name' => '2',
            'price' => '3',
            'qty_ordered' => '5',
            'created_at' => '6',
            'stock_status' => '7'
        ];
        return $sort;
    }

    /**
     * Get sort by
     *
     * @return mixed|string
     */
    public function getOrderDefault()
    {
        $sortby = $this->getAvailableOrders();
        return $sortby[$this->data->getSortby()];
    }

    /**
     * Get config sort by value
     *
     * @return mixed
     */
    public function getSortby()
    {
        return $this->data->getSortby();
    }
    /**
     * Get data of items
     *
     * @param $item
     * @param $itemsData
     * @return void
     * @throws NoSuchEntityException
     */
    public function getDataItems($item, &$itemsData)
    {
        $product = $item->getProduct();
        $store = $this->storeManager->getStore();
        $imageUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        $itemsData[] = [
            "sku" => $item->getSku(),
            "name" => $product->getName(),
            "price" => $item->getData("price"),
            "product_type" => $product->getTypeId(),
            "stock_status" => $product->getStatus(),
            "qty_order" => $item->getReoderQtyOrdered(),
            'image' => $imageUrl,
            "create_at" => $item->getCreatedAt(),
            "currency" => $this->getCurrentCurrencySymbol(),
        ];
    }

    /**
     * Process data items
     *
     * @param $args
     * @param $items
     * @param $orderSort
     * @return array
     * @throws NoSuchEntityException
     */
    public function getInfoItemsReorder($args, $items, $orderSort)
    {
        $itemsData = [];
        $currentPage = $args['currentPage'];
        $this->initializeData($args);
        $countItems = count($items);
        $start = ($currentPage - 1) * $args['pageSize'];
        $end = $currentPage * $args['pageSize'] - 1;
        if ($end > ($countItems - 1)) {
            $end = $countItems - 1;
        }
        $count = -1;

        foreach ($items as $item) {
            $count++;
            if ($count >= $start && $count <= $end) {
                $this->getDataItems($item, $itemsData);
            }
        }
        return $itemsData;
    }

    /**
     * get items form $customerId, process paging
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws Exception
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        $customerId = $context->getUserId();
        if ($customerId == 0) {
            throw new Exception(__('Please login '));
        }
        $this->initializeData($args);
        $this->validate($args);
        $order = $this->reorderProduct->getItems($customerId);
        $items = $order->getItems();
        $ordersort = $this->getOrderDefault();
        if (count($items)>0) {
            switch ($ordersort) {
                case "2":
                    $ordersort='name';
                    usort($items, fn ($sortName1, $sortName2) => strcmp($sortName1[$ordersort], $sortName2[$ordersort]));
                    break;
                case "3":
                    $ordersort='price';
                    usort($items, function ($sortPrice1, $sortPrice2) {
                        return (float)$sortPrice1['price'] - (float)$sortPrice2['price'];
                    });
                    break;
                case "5":
                    $ordersort='qty_ordered';
                    usort($items, function ($sortQtyOrder1, $sortQtyOrder2) {
                        return $sortQtyOrder1['reoder_qty_ordered'] - $sortQtyOrder2['reoder_qty_ordered'];
                    });
                    break;
                case "6":
                    $ordersort='created_at';
                   $product = $items = $order->getItems();
                    usort($product, function ($sortCreate_at1, $sortCreate_at2) {
                        return strtotime($sortCreate_at1['created_at']) -strtotime($sortCreate_at2['created_at']);
                    });
                    break;
            }
        }
        return $this->getInfoItemsReorder($args, $items, $ordersort);
    }
}
