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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Helper;

use Bss\CustomPricing\Api\Data\ProductPriceInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Product Price Indexer Helper
 *
 * @since 1.0.7
 */
class IndexHelper
{
    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Bss\CustomPricing\Model\Indexer\PriceRule
     */
    protected $priceRuleIndexer;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\Indexer\Backlog
     */
    protected $backlogResolver;

    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param \Magento\Framework\Indexer\IndexerInterface $indexer
     * @param \Bss\CustomPricing\Model\Indexer\PriceRule $priceRuleIndexer
     * @param \Bss\CustomPricing\Model\ResourceModel\Indexer\Backlog $backlogResolver
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerInterface $indexer,
        \Bss\CustomPricing\Model\Indexer\PriceRule $priceRuleIndexer,
        \Bss\CustomPricing\Model\ResourceModel\Indexer\Backlog $backlogResolver,
        ProductPriceRepositoryInterface $productPriceRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->indexer = $indexer;
        $this->priceRuleIndexer = $priceRuleIndexer;
        $this->backlogResolver = $backlogResolver;
        $this->productPriceRepository = $productPriceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Clean index value without reindex all
     *
     * @param int|array|null $pPriceId
     * @param int|null $ruleId
     * @param int|null $pId - Product ID
     */
    public function cleanIndex($pPriceId = null, int $ruleId = null, int $pId = null)
    {
        try {
            $select = $this->resourceConnection->getConnection()->select()
                ->from(
                    ['i' => $this->resourceConnection->getTableName(
                        \Bss\CustomPricing\Model\Indexer\IndexerAction::BSS_INDEX_TABLE_NAME
                    )],
                    []
                );

            if ($pPriceId !== null) {
                if (!is_array($pPriceId)) {
                    $pPriceId = [$pPriceId];
                }
                $select->where("i.id IN (?)", $pPriceId);
            }
            if ($ruleId !== null) {
                $select->where("i.rule_id = ?", $ruleId);
            }
            if ($pId !== null) {
                $select->where("i.product_id = ?", $pId);
            }

            $query = $select->deleteFromSelect('i');
            $this->resourceConnection->getConnection()->query($query);
        } catch (\Exception $e) {}
    }

    /**
     * Reindex by provided rule id
     *
     * @param int $ruleId
     */
    public function reindexByRule(int $ruleId)
    {
        $this->searchCriteriaBuilder->addFilter(ProductPriceInterface::RULE_ID, $ruleId);
        $list = $this->productPriceRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
        $ids = [];

        foreach ($list->getItems() as $item) {
            $ids[] = $item->getId();
        }

        $this->reindex($ids);
    }

    /**
     * Reindex provided product price id base on index config
     *
     * @param array|int|string $ids
     */
    public function reindex($ids)
    {
        if (empty($ids)) {
            return;
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $isSchedule = $this->indexer->load(\Bss\CustomPricing\Model\Indexer\PriceRule::INDEX_ID)->isScheduled();
        if (!$isSchedule) {
            $this->priceRuleIndexer->executeList($ids);
        }

        if ($isSchedule) {
            $this->backlogResolver->setBacklog($ids);
            $this->indexer->invalidate();
        }
    }
}
