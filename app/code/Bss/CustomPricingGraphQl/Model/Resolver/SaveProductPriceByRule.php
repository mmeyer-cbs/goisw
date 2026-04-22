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
use Bss\CustomPricing\Model\Config\Source\ProductType;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class SaveProductPriceByRule
 * Mass update product price
 */
class SaveProductPriceByRule extends SaveProductPrice implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->isAllowed($context);
        $this->validate($args);
        $args = $args['input'];

        $priceRule = $this->priceRuleRepository->getById($args['id']);

        if (!$priceRule->getId()) {
            throw new GraphQlNoSuchEntityException(__("We did not find the rule id you provided."));
        }

        $this->prepareFilter($args);

        $productPrices = $this->productPriceRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        foreach ($productPrices->getItems() as $productPrice) {
            $this->updateProductPrice($productPrice, $args);
        }

        // Just apply price method for rule when set custom for all product in rule
        // if (!isset($args['product_ids']) || !empty($args['product_ids'])) {
        //    $priceRule->setPriceMethod(sprintf("%s_%s", $args['price_type'], $args['custom_price']));

        //    $priceRule->setDefaultPriceMethod()
        //    $this->priceRuleRepository->save($priceRule);
        // }

        return $this->priceRuleRepository->getById(
            $args['id'],
            $this->getRelationFields()
        );
    }

    /**
     * Prepare product price collection filter
     *
     * @param array $args
     */
    public function prepareFilter(array $args)
    {
        if (isset($args['product_ids']) && !empty($args['product_ids'])) {
            $this->searchCriteriaBuilder->addFilter(ProductPriceInterface::PRODUCT_ID, $args['product_ids'], 'in');
        }

        $this->searchCriteriaBuilder->addFilter(ProductPriceInterface::RULE_ID, $args['id']);

        // Fetch only product types that be allowed to update
        $this->searchCriteriaBuilder->addFilter(ProductPriceInterface::TYPE_ID, ProductType::getNoNeedUpdatePType(), 'nin');
    }
}
