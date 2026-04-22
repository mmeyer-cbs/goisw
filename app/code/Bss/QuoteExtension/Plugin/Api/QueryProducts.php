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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Plugin\Api;

use Bss\QuoteExtension\Model\Api as ModelApi;

/**
 * Class CategoryQuoteExtension
 *
 * @package Bss\QuoteExtension\Plugin
 */
class QueryProducts
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var null|int
     */
    protected $customerGroupId = null;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $ModelApi;

    /**
     * QueryProducts constructor.
     *
     * @param ModelApi $ModelApi
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $helperData,
        ModelApi $ModelApi
    ) {
        $this->helperData = $helperData;
        $this->ModelApi = $ModelApi;
    }

    /**
     * Add extension attribute "disable_add_to_cart" and "can_show_price"
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
    ) {
        if ($this->helperData->isEnable()) {
            $extensionAttributes = $entity->getExtensionAttributes();
            /** get current extension attributes from entity **/
            $extensionAttributes->setIsActiveRequest4QuoteProductPage($entity->getIsActiveRequest4QuoteProductPage());
            $entity->setExtensionAttributes($extensionAttributes);
        }
        return $entity;
    }

    /**
     * Add extension attribute is_active_4_quote_product
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ) : \Magento\Catalog\Api\Data\ProductSearchResultsInterface {
        $products = [];
        foreach ($searchCriteria->getItems() as $entity) {
            $extensionAttributes = $entity->getExtensionAttributes();
            if ($this->helperData->isEnable() && $this->helperData->isActiveRequest4Quote($entity)) {
                $extensionAttributes->setIsActiveRequest4QuoteProductPage(true);
            } else {
                $extensionAttributes->setIsActiveRequest4QuoteProductPage(false);
            }

            $entity->setExtensionAttributes($extensionAttributes);
            $products[] = $entity;
        }
        $searchCriteria->setItems($products);
        return $searchCriteria;
    }
}
