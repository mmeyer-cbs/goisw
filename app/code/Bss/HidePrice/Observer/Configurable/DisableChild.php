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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Observer\Configurable;

use Bss\HidePrice\Model\Config\Source\ApplyForChildProduct;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Apply Disabled to Child
 *
 * @package Bss\CatalogPermission\Observer\Page
 */
class DisableChild implements ObserverInterface
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configure;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $modelProduct;

    /**
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configure
     * @param \Magento\Catalog\Model\Product $modelProduct
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configure,
        \Magento\Catalog\Model\Product $modelProduct
    ) {
        $this->configure = $configure;
        $this->modelProduct = $modelProduct;
    }

    /**
     * Apply Disabled to Child
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getProduct();
        $childProductId = $product->getId();
        $parentProductId = $this->configure->getParentIdsByChild($childProductId);
        if ($parentProductId) {
            foreach ($parentProductId as $id) {
                $parentProduct = $this->modelProduct->load($id);
                if ($parentProduct->getHidepriceApplychild() !== ApplyForChildProduct::BSS_HIDE_PRICE_NO) {
                    $product->lockAttribute('hideprice_message');
                    $product->lockAttribute('hideprice_customergroup');
                    $product->lockAttribute('hideprice_url');
                    $product->lockAttribute('hideprice_action');
                }
            }
        }
    }
}
