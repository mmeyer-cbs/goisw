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
 * @copyright  Copyright (c) 2020-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Model;

use Bss\CustomPricing\Api\Data\PriceRuleInterface;
use Bss\CustomPricing\Model\ResourceModel\PriceRule as ResourceModel;
use Bss\CustomPricing\Model\Rule\Condition\Customer\Combine;
use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * The price rule model class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PriceRule extends AbstractModel implements PriceRuleInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'bss_custom_pricing';

    /**
     * @var Combine
     */
    protected $customerConditions;

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'price_rule';

    /**
     * @var Rule\Condition\Product\CombineFactory
     */
    protected $productCondCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * @var Rule\Condition\Customer\CombineFactory
     */
    protected $customerCondCombineFactory;

    /**
     * @var array
     */
    protected $relatedProducts = [];

    /**
     * @var array
     */
    protected $relatedCustomers = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * @var array
     */
    protected $productAttributes = [];

    /**
     * @var array
     */
    protected $customerAttributes = [];

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var Indexer\PriceRule
     */
    protected $priceRuleIndexer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * The relation fields - support `applied_customers`, `applied_products`
     *
     * @var array
     */
    protected $with = [];

    /**
     * The relation fields - support `applied_customers`, `applied_products`
     *
     * @var \Bss\CustomPricing\Api\RelatedRepositoryInterface[]
     */
    protected $hasManyFields = [];

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * PriceRule Constructor
     *
     * @param \Bss\CustomPricing\Helper\Data $helperData
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Rule\Condition\Product\CombineFactory $productCondCombineFactory
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory
     * @param Rule\Condition\Customer\CombineFactory $customerCondCombineFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Bss\CustomPricing\Model\ResourceModel\Product $productResource
     * @param \Magento\Framework\Indexer\IndexerInterface $indexer
     * @param Indexer\PriceRule $priceRuleIndexer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Bss\CustomPricing\Api\RelatedRepositoryInterface[] $hasManyFields
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\CustomPricing\Helper\Data $helperData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        Rule\Condition\Product\CombineFactory $productCondCombineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        Rule\Condition\Customer\CombineFactory $customerCondCombineFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Bss\CustomPricing\Model\ResourceModel\Product $productResource,
        \Magento\Framework\Indexer\IndexerInterface $indexer,
        Indexer\PriceRule $priceRuleIndexer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $hasManyFields,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->productCondCombineFactory = $productCondCombineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->customerCondCombineFactory = $customerCondCombineFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
        $this->resourceIterator = $resourceIterator;
        $this->productResource = $productResource;
        $this->indexer = $indexer;
        $this->priceRuleIndexer = $priceRuleIndexer;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->hasManyFields = $hasManyFields;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Init Price Rule model
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
        $this->setIdFieldName(self::ID);
    }

    /**
     * @inheritDoc
     */
    protected function _resetConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('product');
        $this->setConditions($conditions);

        return $this;
    }

    /**
     * Reset rule combine customer conditions
     *
     * @param null|Combine $conditions
     * @return $this
     */
    protected function resetCustomerConditions($conditions = null)
    {
        if ($conditions == null) {
            $conditions = $this->getCustomerConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('customer');
        $this->setCustomerConditions($conditions);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasProductSerialized()) {
            $conditions = $this->getProductConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsProductSerialized();
        }

        return $this->_conditions;
    }

    /**
     * Set rule customer combine conditions model
     *
     * @param Combine $conditions
     *
     * @return $this
     */
    public function setCustomerConditions($conditions)
    {
        $this->customerConditions = $conditions;
        return $this;
    }

    /**
     * Retrieve rule customer combine conditions model
     *
     * @return Combine
     */
    public function getCustomerConditions()
    {
        if (empty($this->customerConditions)) {
            $this->resetCustomerConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasCustomerSerialized()) {
            $conditions = $this->getCustomerConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->customerConditions->loadArray($conditions);
                }
            }
            $this->unsCustomerSerialized();
        }

        return $this->customerConditions;
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->productCondCombineFactory->create();
    }

    /**
     * Get rule customer condition combine model instance
     *
     * @return Combine
     */
    public function getCustomerConditionsInstance()
    {
        return $this->customerCondCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Get conditions fieldset id
     *
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Validate price rule data
     *
     * @param \Magento\Framework\DataObject $dataObject
     *
     * @return bool|string[]
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = [];
        if ($dataObject->hasWebsiteId()) {
            $websiteIds = $dataObject->getWebsiteId();
            if (empty($websiteIds)) {
                $result[] = __('Please specify a website.');
            }
        }
        return !empty($result) ? $result : true;
    }

    /**
     * @inheritDoc
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        $this->loadConditionsData($arr);
        return $this;
    }

    /**
     * Load conditions data
     *
     * @param array $flatData
     * @return $this
     */
    public function loadConditionsData($flatData)
    {
        if (isset($flatData['product'])) {
            $this->getConditions()->setConditions([])->loadArray(reset($flatData['product']), 'product');
        }
        if (isset($flatData['customer'])) {
            $this->getCustomerConditions()->setConditions([])->loadArray(reset($flatData['customer']), 'customer');
        }

        return $this;
    }

    /**
     * Initialize rule model data from array
     *
     * @param array $data
     * @param array $arr
     * @return array|mixed
     * @throws \Exception
     */
    protected function _convertFlatToRecursive(array $data, $arr = [])
    {
        foreach ($data as $key => $value) {
            if (($key === 'product' || $key === 'actions' || $key === 'customer') && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * Convert dates into \DateTime
                 */
                if (in_array($key, ['from_date', 'to_date'], true) && $value) {
                    $value = new \DateTime($value);
                }
                $this->setData($key, $value);
            }
        }

        return $arr;
    }

    /**
     * Some awesome cleared describe the code above
     *
     * @return PriceRule|void
     */
    public function beforeSave()
    {
        // Serialize product conditions
        if ($this->getConditions()) {
            $this->setProductConditionsSerialized($this->serializer->serialize($this->getConditions()->asArray()));
            $this->_conditions = null;
        }

        // Serialize customer conditions
        if ($customerConditions = $this->getCustomerConditions()) {
            $this->setCustomerConditionsSerialized(
                $this->serializer->serialize(
                    $customerConditions->asArray()
                )
            );
            $this->customerConditions = null;
        }

        \Magento\Framework\Model\AbstractModel::beforeSave();
        return $this;
    }

    /**
     * Price rule is ready to create
     *
     * @return bool
     */
    public function isCompleteToCreate()
    {
        return $this->getName() && $this->getWebsiteId();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($val)
    {
        return $this->setData(self::NAME, $val);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($val)
    {
        return $this->setData(self::STATUS, $val);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($val)
    {
        return $this->setData(self::DESCRIPTION, $val);
    }

    /**
     * @inheritDoc
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    /**
     * @inheritDoc
     */
    public function setPriority($val)
    {
        return $this->setData(self::DESCRIPTION, $val);
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setWebsiteId($val)
    {
        return $this->setData(self::WEBSITE_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setConditionsSerialized($val)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $val);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerConditionsSerialized()
    {
        return $this->getData(self::CUSTOMER_CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerConditionsSerialized($val)
    {
        return $this->setData(self::CUSTOMER_CONDITIONS_SERIALIZED, $val);
    }

    /**
     * @inheritDoc
     */
    public function getProductConditionsSerialized()
    {
        return $this->getData(self::PRODUCT_CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setProductConditionsSerialized($val)
    {
        return $this->setData(self::PRODUCT_CONDITIONS_SERIALIZED, $val);
    }

    /**
     * @inheritDoc
     */
    public function getIsNotLoggedRule()
    {
        return $this->getData(self::IS_NOT_LOGGED_RULE);
    }

    /**
     * @inheritDoc
     */
    public function setIsNotLoggedRule($val)
    {
        return $this->setData(self::IS_NOT_LOGGED_RULE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultPriceMethod()
    {
        return $this->getData(self::DEFAULT_PRICE_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultPriceMethod($val)
    {
        return $this->setData(self::DEFAULT_PRICE_METHOD, $val);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultPriceValue()
    {
        return $this->getData(self::DEFAULT_PRICE_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultPriceValue(?float $value)
    {
        return $this->setData(self::DEFAULT_PRICE_VALUE, $value);
    }

    /**
     * Get rule id
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->getId();
    }

    /**
     * Get product data array with custom input field
     *
     * @param array|string|null $attributes
     * @return array
     */
    public function getListProductData($attributes = null)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($this->_getStoreByWebsite($this->getWebsiteId()));
        $product = $this->productFactory->create();
        $this->relatedProducts = [];
        $this->setCollectedAttributes([]);
        $this->getConditions()->collectValidatedAttributes($productCollection);

        if ($attributes && !is_array($attributes)) {
            $attributes = [$attributes];
        }
        $this->productAttributes = $attributes;
        $this->resourceIterator->walk(
            $productCollection->addAttributeToSelect($this->productAttributes, 'left')->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'attributes' => $this->getCollectedAttributes(),
                'product' => $product
            ]
        );
        return $this->relatedProducts;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function callbackValidateProduct($args)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->_getWebsitesMap();
        foreach ($websites as $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($this->getConditions()->validate($product)) {
                switch ($product->getTypeId()) {
                    case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                        $productTypeInstance = $product->getTypeInstance();
                        $relatedProducts = $productTypeInstance->getUsedProducts($product);
                        foreach ($relatedProducts as $relatedProduct) {
                            $this->mappingProductData($relatedProduct);
                        }
                        break;
                    case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                    case \Magento\Bundle\Model\Product\Type::TYPE_CODE:
                        // No add child product for fixed price type bundle product
                        if ($product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE
                            && $this->productResource->isFixedPriceType($product->getId())
                        ) {
                            break;
                        }
                        $childrenGroupIds = $product->getTypeInstance()->getChildrenIds($product->getId(), false);
                        //@codingStandardsIgnoreLine
                        $childrenIds = array_merge([], ...$childrenGroupIds);
                        $children = $this->productCollectionFactory->create()
                            ->addAttributeToSelect($this->productAttributes, 'left')
                            ->addFieldToFilter('entity_id', ['in' => $childrenIds]);
                        foreach ($children as $child) {
                            $this->mappingProductData($child);
                        }
                        break;
                    default:
                        break;
                }
                $this->mappingProductData($product);
            }
        }
    }

    /**
     * Mapping product data
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    private function mappingProductData($product)
    {
        foreach ($this->productAttributes as $attribute) {
            $productData[$attribute] = $product->getData($attribute);
            $this->relatedProducts[$product->getId()] = $productData;
        }
    }

    /**
     * Get product data array with custom input field
     *
     * @param array|string|null $attributes
     * @param int $websiteId
     * @return array
     * @deprecated
     */
    public function getListCustomersData($attributes = null, $websiteId = null)
    {
        return $this->getListCustomersDataCompatible($websiteId, $attributes);
    }

    /**
     * Function getListCustomersData Compatible PHP 8.
     *
     * @param array|string|null $websiteId
     * @param int $attributes
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListCustomersDataCompatible($websiteId = null, $attributes = null)
    {
        $customerCollection = $this->customerCollectionFactory->create();
        if ($this->helperData->isScopeCustomerPerWebsite()) {
            $customerCollection->addAttributeToFilter("website_id", ["eq" => $websiteId]);
        }

        $customer = $this->customerFactory->create();
        $this->relatedCustomers = [];
        $this->setCollectedAttributes([]);
        $this->getCustomerConditions()->collectValidatedAttributes($customerCollection);

        if ($attributes && !is_array($attributes)) {
            $attributes = [$attributes];
        }
        $this->customerAttributes = $attributes;
        $this->resourceIterator->walk(
            $customerCollection->addAttributeToSelect($this->customerAttributes, '')->getSelect(),
            [[$this, 'callbackValidateCustomer']],
            [
                'attributes' => $this->getCollectedAttributes(),
                'customer' => $customer
            ]
        );
        return $this->relatedCustomers;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateCustomer($args)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = clone $args['customer'];
        $customer->setData($args['row']);

        if ($this->getCustomerConditions()->validate($customer)) {
            foreach ($this->customerAttributes as $attribute) {
                $customerData[$attribute] = $customer->getData($attribute);
                $this->relatedCustomers[$customer->getId()] = $customerData;
            }
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * Get default store_id by website_id
     *
     * @param int $websiteId
     * @return int
     */
    protected function _getStoreByWebsite($websiteId)
    {
        $defaultStoreId = 0;
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            if ($website->getId() != $websiteId || $website->getDefaultStore() === null) {
                continue;
            }
            $defaultStoreId = $website->getDefaultStore()->getId();
        }
        return $defaultStoreId;
    }

    /**
     * Need to process the customer
     *
     * @return bool
     */
    public function needProcessCustomers()
    {
        $isNotLoggedRule = $this->getIsNotLoggedRule();
        if (!$isNotLoggedRule) {
            return true;
        }
        $conditions = $this->getCustomerConditionsSerialized();
        if (!empty($conditions)) {
            $conditions = $this->serializer->unserialize($conditions);
            if (!isset($conditions['conditions'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\InputException
     */
    public function afterSave()
    {
        $this->indexer->load(Indexer\PriceRule::INDEX_ID);
        $this->indexer->invalidate();
        return parent::afterSave();
    }

    /**
     * Set relation fields
     *
     * @param array|string $fields
     * @return $this
     */
    public function with($fields)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        foreach ($fields as $field) {
            if (!in_array($field, $this->with)) {
                array_push($this->with, $field);
            }
        }
        return $this;
    }

    /**
     * Eager loading relations data
     *
     * @param PriceRule $object
     * @return PriceRule
     */
    public function reloadRelations($object)
    {
        if ($object->getId()) {
            foreach ($this->with as $field) {
                if (isset($this->hasManyFields[$field])) {
                    if ($object->hasData($field)) {
                        continue;
                    }

                    $searchCriteria = $this->searchCriteriaBuilder->addFilter("rule_id", $object->getId());
                    $object->setData(
                        $field,
                        $this->hasManyFields[$field]->getList($searchCriteria->create())->getItems()
                    );
                }
            }
        }

        return $object;
    }

    /**
     * Load the relations field collection
     *
     * @return $this|PriceRule
     */
    public function afterLoad()
    {
        $object = parent::afterLoad();
        $this->reloadRelations($object);

        return $object;
    }
}
