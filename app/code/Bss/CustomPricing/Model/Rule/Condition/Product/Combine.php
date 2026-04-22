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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\Rule\Condition\Product;

use Bss\CustomPricing\Model\Rule\Condition\Product\Product as ModuleProductConditionDataModel;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CatalogRuleCombine;
use Magento\CatalogRule\Model\Rule\Condition\Product as CatalogRuleProductConditionDataModel;

/**
 * Class Combine for product conditions
 */
class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    protected $elementName = 'general_information[rule]';

    /**
     * Combine constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $conditionFactory,
            $data
        );
        $this->setType(Combine::class);
    }

    /**
     * @inheritDoc
     */
    public function getNewChildSelectOptions()
    {
        return array_map(
            [$this, "remappingConditionsClass"],
            parent::getNewChildSelectOptions()
        );
    }

    /**
     * Some awesome cleared describe the code above
     *
     * @param array $item
     *
     * @return mixed
     */
    protected function remappingConditionsClass($item)
    {
        if ($item['value'] === null) {
            $item["value"] = '';
            return $item;
        }

        if (is_array($item['value'])) {
            foreach ($item["value"] as $key => $_item) {
                $_item["value"] = str_replace(
                    CatalogRuleProductConditionDataModel::class,
                    ModuleProductConditionDataModel::class,
                    $_item["value"]
                );
                $item["value"][$key] = $_item;
            }
        } else {
            $item["value"] = str_replace(
                CatalogRuleCombine::class,
                self::class,
                $item["value"]
            );
        }
        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getConditions()
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'product';
        return $this->getData($key);
    }

    /**
     * @inheritDoc
     */
    public function setConditions($cond)
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'product';
        return $this->setData($key, $cond);
    }
}
