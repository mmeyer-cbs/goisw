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
 * @category  BSS
 * @package   Bss_ReorderProductGraphQl
 * @author    Extension Team
 * @copyright Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ReorderProductGraphQl\Model\Resolver;

use Bss\ReorderProduct\Helper\Data;
use Magento\Framework\GraphQl\Config\Element\Field as FieldAlias;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Use get config in Amin/Configuration/Bss/ReorderProduct use graphql
 */
class GetConfigOutput implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * Constructor
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * Get config and return output
     *
     * @param FieldAlias $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        FieldAlias  $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        return [
            "active" => $this->data->isActive(),
            "redirect_cart" => $this->data->isRedirecttocart(),
            "redirect_wishlist" => $this->data->isRedirecttowishlist(),
            "ignore_buy_request" => $this->data->getIgnoreBuyRquestParam(),
            "btnwishlist" => $this->data->showbtnWishlist(),
            "show_quickview" => $this->data->showSku(),
            "show_sku" => $this->data->showbtnWishlist(),
            "qty_inventory" => $this->data->showQtyInventory(),
            "list_per_page_values" => $this->data->getListperpagevalue(),
            "list_per_page" => $this->data->getListperpage(),
            "sort_by" => $this->data->getSortby(),
            "list_allow_all" => $this->data->showAlllist(),
        ];
    }
}
