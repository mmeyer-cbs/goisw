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
namespace Bss\CustomPricing\Ui\DataProvider\PriceRule;

use Bss\CustomPricing\Model\ResourceModel\PriceRule\CollectionFactory;
use Bss\CustomPricing\Api\Data\PriceRuleInterface as PriceRule;

/**
 * Data provider for price rule listing
 */
class PriceRuleDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Price rule collection
     *
     * @var \Bss\CustomPricing\Model\ResourceModel\PriceRule\Collection
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        return $this->getCollection()->toArray();
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() !== "fulltext") {
            $this->collection->addFieldToFilter(
                $filter->getField(),
                [
                    $filter->getConditionType() => $filter->getValue()
                ]
            );
        } else {
            $value = trim($filter->getValue());
            $collection = $this->getCollection();
            $collection->addFieldToFilter(
                [
                    PriceRule::ID,
                    PriceRule::NAME,
                    PriceRule::STATUS,
                    PriceRule::PRIORITY,
                    PriceRule::DESCRIPTION,
                ],
                [
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"]
                ]
            );
            $this->collection = $collection;
        }
        return;
    }
}
