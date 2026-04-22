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

use Magento\Framework\Indexer\Dimension;

/**
 * Interface ProductTypeIndexerInterface
 */
interface ProductTypeIndexerInterface
{
    /**
     * Index execution
     *
     * @param Dimension[] $dimensions
     * @param array $changedData Struct exam ["type_id" => "simple", "changed_product_ids" => [1,2,3], "rule_id" => 1]
     * @throws \Exception
     */
    public function executeIndex($dimensions, $changedData);
}
