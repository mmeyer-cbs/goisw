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

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class GetPriceRule
 * Get price rule by id
 */
class GetPriceRule extends AbstractPriceRuleResolver implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validate($args);

        return $this->priceRuleRepository->getById(
            $args["rule_id"],
            $this->getRelationFields()
        );
    }

    /**
     * Validate input
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    protected function validate($args)
    {
        if (!isset($args["rule_id"]) || !$args["rule_id"]) {
            throw new GraphQlInputException(__("The \"rule_id\" should be defined."));
        }
    }
}
