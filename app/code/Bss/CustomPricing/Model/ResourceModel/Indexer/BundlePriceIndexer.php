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
 * @package    Bss_
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\ResourceModel\Indexer;

use Bss\CustomPricing\Helper\Data as ModuleHelper;
use Bss\CustomPricing\Model\ResourceModel\ProductPrice as ProductPriceResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer as CoreTableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Bundle product type price indexer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundlePriceIndexer extends AbstractPriceIndexer implements ProductTypeIndexerInterface
{
    const BSS_CATALOG_INDEX_TIER_PRICE = "bcp_product_index_tier_price";
    const BSS_CATALOG_ENTITY_TIER_PRICE = "bcp_product_entity_tier_price";
    const BSS_CATALOG_INDEX_BUNDLE_TMP = "bcp_product_index_price_bundle_tmp";
    const BSS_CATALOG_INDEX_BUNDLE_OPT_TMP = "bcp_product_index_price_bundle_opt_tmp";
    const BSS_CATALOG_INDEX_BUNDLE_SEL_TMP = "bcp_product_index_price_bundle_sel_tmp";

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @inheritDoc
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        ModuleHelper $helper,
        JoinAttributeProcessor $joinAttributeProcessor,
        BasePriceModifier $basePriceModifier,
        CoreTableMaintainer $tableMaintainer,
        \Magento\Framework\Module\Manager $moduleManager,
        $connectionName = 'indexer'
    ) {
        parent::__construct(
            $metadataPool,
            $resource,
            $helper,
            $joinAttributeProcessor,
            $basePriceModifier,
            $tableMaintainer,
            $connectionName
        );
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritDoc
     */
    public function executeIndex($dimensions, $changedData)
    {
        $this->prepareTierPriceIndex($changedData);

        $this->prepareBundlePriceTable();

        $this->prepareBundlePriceByType(
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED,
            $changedData
        );

        $this->prepareBundlePriceByType(
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC,
            $changedData
        );

        $this->calculateBundleOptionPrice($dimensions, $changedData);

        $this->basePriceModifier->modifyPrice(
            $this->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME),
            $changedData
        );
    }

    /**
     * Retrieve temporary price index table name for fixed bundle products
     *
     * @return string
     */
    private function getBundlePriceTable()
    {
//        return $this->getTable('catalog_product_index_price_bundle_tmp');
        return $this->getTable(self::BSS_CATALOG_INDEX_BUNDLE_TMP);
    }

    /**
     * Retrieve table name for temporary bundle selection prices index
     *
     * @return string
     */
    private function getBundleSelectionTable()
    {
        return $this->getTable(self::BSS_CATALOG_INDEX_BUNDLE_SEL_TMP);
    }

    /**
     * Retrieve table name for temporary bundle option prices index
     *
     * @return string
     */
    private function getBundleOptionTable()
    {
//        return $this->getTable('catalog_product_index_price_bundle_opt_tmp');
        return $this->getTable(self::BSS_CATALOG_INDEX_BUNDLE_OPT_TMP);
    }

    /**
     * Prepare temporary price index table for fixed bundle products
     *
     * @return $this
     */
    private function prepareBundlePriceTable()
    {
        $orgBundlePriceTbl = $this->getTable('catalog_product_index_price_bundle_tmp');
        $this->getConnection()->createTemporaryTableLike(
            $this->getTable(self::BSS_CATALOG_INDEX_BUNDLE_TMP),
            $orgBundlePriceTbl,
            true
        );
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle selection prices index
     *
     * @return $this
     */
    private function prepareBundleSelectionTable()
    {
        $orgBundlePriceTbl = $this->getTable('catalog_product_index_price_bundle_sel_tmp');
        $this->getConnection()->createTemporaryTableLike(
            $this->getTable(self::BSS_CATALOG_INDEX_BUNDLE_SEL_TMP),
            $orgBundlePriceTbl,
            true
        );
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle option prices index
     *
     * @return $this
     */
    private function prepareBundleOptionTable()
    {
        $orgBundlePriceTbl = $this->getTable('catalog_product_index_price_bundle_opt_tmp');
        $this->getConnection()->createTemporaryTableLike(
            $this->getTable(self::BSS_CATALOG_INDEX_BUNDLE_OPT_TMP),
            $orgBundlePriceTbl,
            true
        );
        return $this;
    }

    /**
     * Prepare temporary price index data for bundle products by price type
     *
     * @param int $priceType
     * @param array $changedData
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function prepareBundlePriceByType($priceType, $changedData)
    {
        $entityIds = $changedData["changed_product_ids"];

        $connection = $this->getConnection();

        $customerGroupExpr = $connection->getCheckSql(
            'rules.is_not_logged_rule = 1',
            'cg.customer_group_id = customers.group_id OR cg.customer_group_id = 0',
            'cg.customer_group_id = customers.group_id'
        );

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->joinLeft(
            ['rules' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\PriceRule::TABLE)],
            sprintf("rules.id = %s", $changedData["rule_id"]),
            []
        )->joinLeft(
            ['ac' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\AppliedCustomers::TABLE)],
            "ac.rule_id = rules.id",
            []
        )->joinLeft(
            ['customers' => $this->getTable("customer_entity")],
            'customers.entity_id = ac.customer_id',
            []
        )->joinInner(
            ['cg' => $this->getTable('customer_group')],
            $customerGroupExpr,
            ['customer_group_id']
        )->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id',
            ['pw.website_id']
        )->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')],
            'pw.website_id = cwd.website_id',
            []
        );
        $select->joinLeft(
            ['tp' => $this->getTable(self::BSS_CATALOG_INDEX_TIER_PRICE)],
            'tp.entity_id = e.entity_id AND tp.website_id = pw.website_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            []
        )->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        );

        // only reindex enable product
        // $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);

        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->joinAttributeProcessor->process($select, 'tax_class_id');
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $select->columns(['tax_class_id' => new \Zend_Db_Expr('0')]);
        } else {
            $select->columns(
                ['tax_class_id' => $connection->getCheckSql($taxClassId . ' IS NOT NULL', $taxClassId, 0)]
            );
        }

        $this->joinAttributeProcessor->process($select, 'price_type', $priceType);

        $price = $this->getCustomPriceExpr($select);
        $priceExpr = $this->getBundlePriceExpr($connection, $select, $price, $priceType);

        $select->columns(
            [
                'price_type' => new \Zend_Db_Expr($priceType),
                'special_price' => $priceExpr["special_price"],
                'tier_percent' => 'tp.min_price',
                'orig_price' => $connection->getIfNullSql($price, '0'),
                'price' => $priceExpr["final_price"],
                'min_price' => $priceExpr["final_price"],
                'max_price' => $priceExpr["final_price"],
                'tier_price' => $priceExpr["tier_price"],
                'base_tier' => $priceExpr["tier_price"],
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($this->getBundlePriceTable());
        $connection->query($query);
    }

    /**
     * Get bundle price expression data
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param Select $select
     * @param \Zend_Db_Expr $price
     * @param int $priceType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    private function getBundlePriceExpr($connection, $select, $price, $priceType)
    {
        $leastSqlData = [$price];
        $lastResult = [
            'final_price' => new \Zend_Db_Expr('0'),
            'special_price' => new \Zend_Db_Expr("NULL"),
            'tier_price' => $this->helper->applyNormalTierPrice() ?
                $connection->getCheckSql('tp.min_price IS NOT NULL', '0', 'NULL') :
                new \Zend_Db_Expr("NULL")
        ];
        if ($specialPriceExpr = $this->getBundleSpecialPriceExpr($connection, $select, $price, $priceType)) {
            if ($specialPriceExpr["special_price_expr"]) {
                $leastSqlData[] = $connection->getIfNullSql($specialPriceExpr["special_price_expr"], $price);
            }
            $lastResult["special_price"] = $connection->getCheckSql(
                $specialPriceExpr["special_condition"],
                $specialPriceExpr["special_price"],
                '0'
            );
        }
        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            if ($tierPriceExpr = $this->getBundleTierPriceExpr($connection, $price)) {
                $leastSqlData[] = $connection->getIfNullSql($tierPriceExpr, $price);
                $lastResult["tier_price"] = $tierPriceExpr;
            }
            if (count($leastSqlData) > 1) {
                $lastResult["final_price"] = $connection->getLeastSql($leastSqlData);
            } else {
                $lastResult["final_price"] = $price;
            }
        }

        return $lastResult;
    }

    /**
     * Get special price expression with module cond
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param Select $select
     * @param \Zend_Db_Expr $price
     * @param int $priceType
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    private function getBundleSpecialPriceExpr($connection, $select, $price, $priceType)
    {
        if ($this->helper->applyNormalSpecialPrice()) {
            $specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');
            $specialFrom = $this->joinAttributeProcessor->process($select, 'special_from_date');
            $specialTo = $this->joinAttributeProcessor->process($select, 'special_to_date');
            $currentDate = new \Zend_Db_Expr('cwd.website_date');

            $specialFromDate = $connection->getDatePartSql($specialFrom);
            $specialToDate = $connection->getDatePartSql($specialTo);
            $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
            $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
            $specialExpr = "{$specialPrice} IS NOT NULL AND {$specialPrice} > 0 AND {$specialPrice} < 100"
                . " AND {$specialFromExpr} AND {$specialToExpr}";

            return [
                'special_price_expr' => $priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED ?
                    $connection->getCheckSql(
                        $specialExpr,
                        'ROUND(' . $price . ' * (' . $specialPrice . '  / 100), 4)',
                        'NULL'
                    ) : false,
                'special_price' => $specialPrice,
                'special_condition' => $specialExpr
            ];
        }
        return false;
    }

    /**
     * Get tier price expression with module cond
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Zend_Db_Expr $price
     * @return \Zend_Db_Expr|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getBundleTierPriceExpr($connection, $price)
    {
        if ($this->helper->applyNormalTierPrice()) {
            $tierExpr = new \Zend_Db_Expr('tp.min_price');
            return $connection->getCheckSql(
                $tierExpr . ' IS NOT NULL',
                'ROUND((1 - ' . $tierExpr . ' / 100) * ' . $price . ', 4)',
                'NULL'
            );
        }
        return false;
    }

    /**
     * Get custom price column
     *
     * @param Select $select
     * @return \Zend_Db_Expr
     * @throws \Exception
     */
    private function getCustomPriceExpr(Select $select)
    {
        $connection = $this->getConnection();
        $productIdField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $tblAlias = 'tad_custom_price';
        $select->joinLeft(
            [$tblAlias => $this->getTable(ProductPriceResource::TABLE)],
            "{$tblAlias}.product_id = e.{$productIdField} AND {$tblAlias}.rule_id = rules.id",
            []
        );
        return $connection->getIfNullSql("{$tblAlias}.custom_price", 0);
    }

    /**
     * Calculate fixed bundle product selections price
     *
     * @param array $dimensions
     * @param array $changedData
     *
     * @return void
     * @throws \Exception
     */
    private function calculateBundleOptionPrice($dimensions, $changedData)
    {
        $connection = $this->getConnection();

        $this->prepareBundleSelectionTable();
        $this->calculateFixedBundleSelectionPrice();
        $this->calculateDynamicBundleSelectionPrice($changedData, $dimensions);

        $this->prepareBundleOptionTable();

        $select = $connection->select()->from(
            $this->getBundleSelectionTable(),
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        )->group(
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        );
        $minPrice = $connection->getCheckSql('is_required = 1', 'price', 'NULL');
        $tierPrice = $connection->getCheckSql('is_required = 1', 'tier_price', 'NULL');
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('MIN(' . $minPrice . ')'),
                'alt_price' => new \Zend_Db_Expr('MIN(price)'),
                'max_price' => $connection->getCheckSql('group_type = 0', 'MAX(price)', 'SUM(price)'),
                'tier_price' => new \Zend_Db_Expr('MIN(' . $tierPrice . ')'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(tier_price)'),
            ]
        );

        $query = $select->insertFromSelect($this->getBundleOptionTable());
        $connection->query($query);

        $this->applyBundlePrice($changedData);
        $this->applyBundleOptionPrice($changedData);
    }

    /**
     * Get base select for bundle selection price
     *
     * @return Select
     * @throws \Exception
     */
    private function getBaseBundleSelectionPriceSelect(): Select
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        return $this->getConnection()->select()->from(
            ['i' => $this->getBundlePriceTable()],
            ['entity_id', 'customer_group_id', 'website_id']
        )->join(
            ['parent_product' => $this->getTable('catalog_product_entity')],
            'parent_product.entity_id = i.entity_id',
            []
        )->join(
            ['bo' => $this->getTable('catalog_product_bundle_option')],
            "bo.parent_id = parent_product.$linkField",
            ['option_id']
        )->join(
            ['bs' => $this->getTable('catalog_product_bundle_selection')],
            'bs.option_id = bo.option_id',
            ['selection_id']
        );
    }

    /**
     * Apply selections price for fixed bundles
     *
     * @return void
     * @throws \Exception
     */
    private function applyFixedBundleSelectionPrice()
    {
        $connection = $this->getConnection();

        $selectionPriceValue = 'bsp.selection_price_value';
        $selectionPriceType = 'bsp.selection_price_type';

        $priceExpr = $this->getSelectionFixedPriceExpr($connection, $selectionPriceType, $selectionPriceValue);

        $select = $this->getBaseBundleSelectionPriceSelect();
        $select->joinInner(
            ['bsp' => $this->getTable('catalog_product_bundle_selection_price')],
            'bs.selection_id = bsp.selection_id AND bsp.website_id = i.website_id',
            []
        )->where(
            'i.price_type=?',
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr["price_expr"],
                'tier_price' => $priceExpr["tier_price"],
            ]
        );
        $query = $select->crossUpdateFromSelect($this->getBundleSelectionTable());
        $connection->query($query);
    }

    /**
     * Calculate selections price for fixed bundles
     *
     * @return void
     * @throws \Exception
     */
    private function calculateFixedBundleSelectionPrice()
    {
        $connection = $this->getConnection();

        $selectionPriceValue = 'bs.selection_price_value';
        $selectionPriceType = 'bs.selection_price_type';

        $priceExpr = $this->getSelectionFixedPriceExpr($connection, $selectionPriceType, $selectionPriceValue);

        $select = $this->getBaseBundleSelectionPriceSelect();
        $select->where(
            'i.price_type=?',
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr["price_expr"],
                'tier_price' => $priceExpr["tier_price"],
            ]
        );
        $query = $select->insertFromSelect($this->getBundleSelectionTable());
        $connection->query($query);

        $this->applyFixedBundleSelectionPrice();
    }

    /**
     * Get final price expression with module condition
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $selectionPriceType
     * @param string $selectionPriceValue
     * @return \Zend_Db_Expr[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSelectionFixedPriceExpr($connection, $selectionPriceType, $selectionPriceValue)
    {
        $price = new \Zend_Db_Expr($selectionPriceValue . " * bs.selection_qty");
        $leastPrice = [$price];
        $lastResult = [
            'price_expr' => $price,
            'tier_price' => new \Zend_Db_Expr("NULL")
        ];
        if ($this->helper->applyNormalSpecialPrice()) {
            $leastPrice[] = new \Zend_Db_Expr(
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.price * (' . $selectionPriceValue . ' / 100),4)',
                    $connection->getCheckSql(
                        'i.special_price > 0 AND i.special_price < 100',
                        'ROUND(' . $selectionPriceValue . ' * (i.special_price / 100),4)',
                        $selectionPriceValue
                    )
                ) . '* bs.selection_qty'
            );
        }
        if ($this->helper->applyNormalTierPrice()) {
            $tierExpr = $connection->getCheckSql(
                'i.base_tier IS NOT NULL',
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.base_tier - (i.base_tier * (' . $selectionPriceValue . ' / 100)),4)',
                    $connection->getCheckSql(
                        'i.tier_percent > 0',
                        'ROUND((1 - i.tier_percent / 100) * ' . $selectionPriceValue . ',4)',
                        $selectionPriceValue
                    )
                ) . ' * bs.selection_qty',
                'NULL'
            );
            $leastPrice[] = $connection->getIfNullSql($tierExpr, $price);
            $lastResult['tier_price'] = $tierExpr;
            // the $price was added in the first is not necessary
            unset($leastPrice[0]);
        }
        if (count($leastPrice) > 1) {
            $lastResult['price_expr'] = $connection->getLeastSql($leastPrice);
        }
        return $lastResult;
    }

    /**
     * Calculate selections price for dynamic bundles
     *
     * @param array $changedData
     * @param array $dimensions
     * @return void
     * @throws \Exception
     */
    private function calculateDynamicBundleSelectionPrice($changedData, $dimensions = [])
    {
        $connection = $this->getConnection();

        $priceExpr = $this->getSelectionDynamicPriceExpr($connection);

        $select = $this->getBaseBundleSelectionPriceSelect();

        $select->join(
            ['mage_idx' => $this->getCoreIdxTable($dimensions)],
            'bs.product_id = mage_idx.entity_id AND i.customer_group_id = mage_idx.customer_group_id' .
            ' AND i.website_id = mage_idx.website_id',
            []
        )->joinLeft(
            ['bss_idx' => $this->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME)],
            'bss_idx.product_id = bs.product_id AND i.customer_group_id = bss_idx.customer_group_id' .
            ' AND i.website_id = bss_idx.website_id AND bss_idx.rule_id = ' . $changedData["rule_id"],
            []
        )->where(
            'i.price_type=?',
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr["price_expr"],
                'tier_price' => $priceExpr["tier_price"],
            ]
        );

        $query = $select->insertFromSelect($this->getBundleSelectionTable());
        $connection->query($query);
    }

    /**
     * Get dynamic price with module condition
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSelectionDynamicPriceExpr($connection)
    {
        $isApplyNormalSpecialPrice = $this->helper->applyNormalSpecialPrice();
        if ($isApplyNormalSpecialPrice) {
            $priceExprFalseStatement = "mage_idx.min_price";
        } else {
            $priceExprFalseStatement = "mage_idx.price";
        }
        // if the product is not index before, then we will get the price base on default magento
        // Nếu như sản phẩm mà chưa được set custom price (đồng nghĩa chưa được index) thì
        // sẽ lấy giá trong bảng của default
        $priceExpr = $connection->getCheckSql(
            "bss_idx.min_price IS NOT NULL",
            "bss_idx.min_price",
            $priceExprFalseStatement
        );

        $price = new \Zend_Db_Expr($priceExpr . ' * bs.selection_qty');

        $leastPrice = [$price];
        $lastResult = [
            'price_expr' => $price,
            'tier_price' => new \Zend_Db_Expr("NULL")
        ];
        if ($isApplyNormalSpecialPrice) {
            $leastPrice[] = $connection->getCheckSql(
                'i.special_price > 0 AND i.special_price < 100',
                'ROUND(' . $price . ' * (i.special_price / 100), 4)',
                $price
            );
        }
        if ($this->helper->applyNormalTierPrice()) {
            $tierExpr = $connection->getCheckSql(
                'i.tier_percent IS NOT NULL',
                'ROUND((1 - i.tier_percent / 100) * ' . $price . ', 4)',
                'NULL'
            );
            $leastPrice[] = $connection->getIfNullSql($tierExpr, $price);
            $lastResult['tier_price'] = $tierExpr;
            // the $price was added in the first is not necessary
            unset($leastPrice[0]);
        }

        if (count($leastPrice) > 1) {
            $lastResult['price_expr'] = $connection->getLeastSql($leastPrice);
        }
        return $lastResult;
    }

    /**
     * Prepare percentage tier price for bundle products
     *
     * @param array $changedData
     * @return void
     * @throws \Exception
     */
    private function prepareTierPriceIndex($changedData)
    {
        $entityIds = $changedData["changed_product_ids"];

        $connection = $this->getConnection();
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $orgTierPriceTbl = $this->getTable('catalog_product_index_tier_price');
        $this->getConnection()->createTemporaryTableLike(
            $this->getTable(self::BSS_CATALOG_INDEX_TIER_PRICE),
            $orgTierPriceTbl,
            true
        );

        // remove index by bundle products
        $select = $connection->select()->from(
            ['i' => $this->getTable(self::BSS_CATALOG_INDEX_TIER_PRICE)],
            null
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "i.entity_id=e.entity_id",
            []
        )->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        );
        $query = $select->deleteFromSelect('i');
        $connection->query($query);

        $select = $connection->select()->from(
            ['tp' => $this->getTable('catalog_product_entity_tier_price')],
            ['e.entity_id']
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "tp.{$linkField} = e.{$linkField}",
            []
        )->join(
            ['cg' => $this->getTable('customer_group')],
            'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
            ['customer_group_id']
        )->join(
            ['pw' => $this->getTable('store_website')],
            'tp.website_id = 0 OR tp.website_id = pw.website_id',
            ['website_id']
        )->where(
            'pw.website_id != 0'
        )->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        )->columns(
            new \Zend_Db_Expr('MIN(tp.value)')
        )->group(
            ['e.entity_id', 'cg.customer_group_id', 'pw.website_id']
        );

        if (!empty($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($this->getTable(self::BSS_CATALOG_INDEX_TIER_PRICE));
        $connection->query($query);
    }

    /**
     * Create bundle price.
     *
     * @param array $changedData
     * @return void
     */
    private function applyBundlePrice($changedData): void
    {
        $select = $this->getConnection()->select();
        $select->from(
            $this->getBundlePriceTable(),
            [
                'product_id' => 'entity_id',
                'rule_id' => new \Zend_Db_Expr($changedData["rule_id"]),
                'customer_group_id',
                'website_id',
                'tax_class_id',
                'orig_price',
                'price',
                'min_price',
                'max_price',
                'tier_price',
            ]
        );

        $query = $select->insertFromSelect($this->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME));
        $this->getConnection()->query($query);

        $this->getConnection()->delete($this->getBundlePriceTable());
    }

    /**
     * Make insert/update bundle option price.
     *
     * @return void
     * @param array $changedData
     */
    private function applyBundleOptionPrice($changedData): void
    {
        $connection = $this->getConnection();

        $subSelect = $connection->select()->from(
            $this->getBundleOptionTable(),
            [
                'entity_id',
                'rule_id' => new \Zend_Db_Expr($changedData["rule_id"]),
                'customer_group_id',
                'website_id',
                'min_price' => new \Zend_Db_Expr('SUM(min_price)'),
                'alt_price' => new \Zend_Db_Expr('MIN(alt_price)'),
                'max_price' => new \Zend_Db_Expr('SUM(max_price)'),
                'tier_price' => new \Zend_Db_Expr('SUM(tier_price)'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(alt_tier_price)'),
            ]
        )->group(
            ['entity_id', 'customer_group_id', 'website_id']
        );

        $minPrice = 'i.min_price + ' . $connection->getIfNullSql('io.min_price', '0');
        $tierPrice = 'i.tier_price + ' . $connection->getIfNullSql('io.tier_price', '0');
        $select = $connection->select()->join(
            ['io' => $subSelect],
            'i.product_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        )->where('i.product_id IN (?)', $changedData["changed_product_ids"])
            ->where('i.rule_id = ?', $changedData["rule_id"])
            ->columns(
                [
                    'min_price' => $connection->getCheckSql("{$minPrice} = 0", 'io.alt_price', $minPrice),
                    'max_price' => new \Zend_Db_Expr('io.max_price + i.max_price'),
                    'tier_price' => $connection->getCheckSql("{$tierPrice} = 0", 'io.alt_tier_price', $tierPrice),
                ]
            );

        $query = $select->crossUpdateFromSelect(['i' => $this->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME)]);

        $connection->query($query);
    }
}
