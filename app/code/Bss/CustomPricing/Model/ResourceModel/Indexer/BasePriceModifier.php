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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\ResourceModel\Indexer;

/**
 * Class BasePriceModifier
 */
class BasePriceModifier
{
    /**
     * @var array
     */
    private $priceModifiers;

    /**
     * BasePriceModifier constructor.
     *
     * @param CustomOptionPriceModifier $customOptionPriceModifier
     * @param ProductPriceIndexModifier $productPriceIndexModifier
     */
    public function __construct(
        CustomOptionPriceModifier $customOptionPriceModifier,
        ProductPriceIndexModifier $productPriceIndexModifier
    ) {
        $this->priceModifiers["customOptionPriceModifier"] = $customOptionPriceModifier;
        // $this->priceModifiers["catalogRulePriceModifier"] = $productPriceIndexModifier;
    }

    /**
     * Modify price data.
     *
     * @param string $ixTblName
     * @param array $changedData
     */
    public function modifyPrice($ixTblName, array $changedData = []) : void
    {
        foreach ($this->priceModifiers as $priceModifier) {
            $priceModifier->modifyPrice($ixTblName, $changedData);
        }
    }
}
