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
declare(strict_types=1);

namespace Bss\CustomPricing\Setup\Patch\Data;

use Bss\CustomPricing\Api\Data\PriceRuleInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Data patch format
 */
class ConvertPriceMethodV107 implements DataPatchInterface
{
    const PRICE_RULES_TABLE = 'bss_price_rules';

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Construct.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Convert price method.
     *
     * @return ConvertPriceMethodV107|void
     */
    public function apply()
    {
        $connection = $this->resourceConnection->getConnection();

        $connection->update(
            $this->resourceConnection->getTableName(self::PRICE_RULES_TABLE),
            [
                PriceRuleInterface::DEFAULT_PRICE_METHOD => new \Zend_Db_Expr("SUBSTRING(price_method,1,POSITION('_' IN price_method) - 1)"),
                PriceRuleInterface::DEFAULT_PRICE_VALUE => new \Zend_Db_Expr("SUBSTRING(price_method, POSITION('_' IN price_method) + 1)")
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Compare ver module.
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.7';
    }
}
