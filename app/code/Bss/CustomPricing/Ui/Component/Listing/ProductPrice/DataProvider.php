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
namespace Bss\CustomPricing\Ui\Component\Listing\ProductPrice;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Controller\RegistryConstants;
use Bss\CustomPricing\Model\ProductPrice;
use Bss\CustomPricing\Model\ResourceModel\ProductPrice\CollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Registry;

/**
 * Custom DataProvider for customer addresses listing
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    const RULE_ID = "id";

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var \Bss\CustomPricing\Model\ProductPriceFactory
     */
    protected $productPriceFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface $request,
     */
    private $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Registry $coreRegistry
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Bss\CustomPricing\Model\ProductPriceFactory $productPriceFactory
     * @param array $meta
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        Registry $coreRegistry,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Bss\CustomPricing\Model\ProductPriceFactory $productPriceFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
        $this->priceRuleRepository = $priceRuleRepository;
        $this->productPriceFactory = $productPriceFactory;
        $this->addByRuleFilter();
    }

    /**
     * Add country key for default billing/shipping blocks on customer addresses tab
     *
     * @return array
     */
    public function getData(): array
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        $this->addByRuleFilter();
        return $this->getCollection()->toArray();
    }

    /**
     * Add rule id filter to collection
     */
    private function addByRuleFilter()
    {
        $ruleId = $this->request->getParam('rule_id');
        $this->getCollection()->addFieldToFilter(
            'rule_id',
            $ruleId
        );
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() !== "fulltext") {
            $this->addByRuleFilter();
            if ($filter->getField() !== "origin_price" && $filter->getField() !== "custom_price") {
                $filter->setConditionType('like');
            }
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
                    ProductPrice::ID,
                    ProductPrice::NAME,
                    ProductPrice::PRODUCT_SKU,
                    ProductPrice::PRODUCT_ID,
                    ProductPrice::ORIGIN_PRICE,
                    ProductPrice::CUSTOM_PRICE,
                    ProductPrice::RULE_ID,
                ],
                [
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
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
