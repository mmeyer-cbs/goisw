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

namespace Bss\CustomPricing\Model\Indexer;

use Bss\CustomPricing\Model\ResourceModel\ProductPrice;

/**
 * Class indexer PriceRule
 */
class PriceRule implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEX_ID = "bss_custom_pricing_index";
    const INDEX_TABLE = "bss_custom_pricing_index";

    /**
     * @var IndexerAction
     */
    protected $indexerAction;

    /**
     * @var ProductPrice
     */
    protected $productPriceResource;

    /**
     * PriceRule constructor.
     *
     * @param IndexerAction $indexerAction
     * @param ProductPrice $productPriceResource
     */
    public function __construct(IndexerAction $indexerAction, ProductPrice $productPriceResource)
    {
        $this->indexerAction = $indexerAction;
        $this->productPriceResource = $productPriceResource;
    }

    /**
     * @inheritDoc
     */
    public function executeFull()
    {
        $this->indexerAction->reindexRows();
    }

    /**
     * @inheritDoc
     */
    public function executeList(array $ids)
    {
        $this->indexerAction->reindexRows($ids);
    }

    /**
     * @inheritDoc
     */
    public function executeRow($id): void
    {
        $this->indexerAction->reindexRows([$id]);
    }

    /**
     * @inheritDoc
     */
    public function execute($ids)
    {
        $this->indexerAction->reindexRows($ids);
    }

    /**
     * Reindex by product or rule
     *
     * @param int $typeId
     * @param string $type
     * @throws \Magento\Framework\Exception\InputException
     */
    public function reindexBy($typeId, $type = 'rule')
    {
        $deleteReindexByRule = true;
        if ($type == "rule") {
            $needIndexIds = $this->productPriceResource->loadBy($typeId);
        }
        if ($type == "product") {
            $needIndexIds = $this->productPriceResource->loadBy(null, $typeId);
            $deleteReindexByRule = false;
        }
        if (isset($needIndexIds) && $needIndexIds) {
            $this->indexerAction->reindexRows($needIndexIds, $deleteReindexByRule);
        }
    }
}
