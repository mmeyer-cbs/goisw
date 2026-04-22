<?php
declare(strict_types=1);
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
 * @package    Bss_CustomPricingGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricingGraphQl\Model\Resolver;


use Bss\CustomPricing\Api\Data\ProductPriceInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class ProductPriceInfo
 * Get all custom price of product in all applied rule
 */
class ProductPriceInfo implements ResolverInterface
{
    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * ProductPriceInfo constructor.
     *
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductPriceRepositoryInterface $productPriceRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productPriceRepository = $productPriceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Add custom price to product info
     *
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['entity_id'])) {
            return [];
        }
        $result = [];

        $this->searchCriteriaBuilder->addFilter(ProductPriceInterface::PRODUCT_ID, $value['entity_id']);
        $productPrices = $this->productPriceRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($productPrices->getItems() as $productPrice) {
            $result[] = [
                'rule_id' => $productPrice->getRuleId(),
                'custom_price' => $productPrice->getCustomPrice()
            ];
        }

        return $result;
    }
}
