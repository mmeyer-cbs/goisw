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

namespace Bss\CustomPricing\Model;

use Bss\CustomPricing\Api\Data\ProductPriceInterface;
use Bss\CustomPricing\Model\ResourceModel\ProductPrice as ResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * The price rule model class
 *
 * @method bool setShouldReindex($value)
 * @method bool getShouldReindex()
 */
class ProductPrice extends AbstractModel implements ProductPriceInterface
{
    /**
     * @var string
     */
    protected $flatProductPriceTable;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'bss_product_price';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'product_price';

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $modHelper;

    /**
     * @var Indexer\PriceRule
     */
    protected $priceRuleIndexer;

    /**
     * ProductPrice constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Indexer\IndexerInterface $indexer
     * @param \Bss\CustomPricing\Helper\Data $modHelper
     * @param Indexer\PriceRule $priceRuleIndexer
     * @param AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Indexer\IndexerInterface $indexer,
        \Bss\CustomPricing\Helper\Data $modHelper,
        Indexer\PriceRule $priceRuleIndexer,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->indexer = $indexer;
        $this->getFlatTableName();
        $this->modHelper = $modHelper;
        $this->priceRuleIndexer = $priceRuleIndexer;
    }

    /**
     * Get index flat table name
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFlatTableName()
    {
        if (!$this->flatProductPriceTable) {
            $this->flatProductPriceTable = $this->_getResource()
                ->getConnection()
                ->getTableName(Indexer\PriceRule::INDEX_TABLE);
        }
        return $this->flatProductPriceTable;
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
    public function getOriginPrice()
    {
        return $this->_getData(self::ORIGIN_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setOriginPrice($val)
    {
        return $this->setData(self::ORIGIN_PRICE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getCustomPrice()
    {
        return $this->_getData(self::CUSTOM_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setCustomPrice($val)
    {
        return $this->setData(self::CUSTOM_PRICE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getRuleId()
    {
        return $this->_getData(self::RULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRuleId($val)
    {
        return $this->setData(self::RULE_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($val)
    {
        return $this->setData(self::PRODUCT_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setProductSku($val)
    {
        return $this->setData(self::PRODUCT_SKU, $val);
    }

    /**
     * @inheritDoc
     */
    public function getTypeId()
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTypeId($val)
    {
        return $this->setData(self::TYPE_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function afterSave()
    {
        $isSchedule = $this->indexer->load(Indexer\PriceRule::INDEX_ID)->isScheduled();
        if (!$this->isObjectNew() && !$isSchedule && $this->getShouldReindex() !== false) {
            $this->priceRuleIndexer->executeRow($this->getId());
        }
        if (!$this->isObjectNew() && $isSchedule) {
            $this->indexer->invalidate();
        }
        return parent::afterSave();
    }

    /**
     * @inheritDoc
     */
    public function getPriceMethod()
    {
        return $this->getData(self::PRICE_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setPriceMethod(int $priceMethod)
    {
        return $this->setData(self::PRICE_METHOD, $priceMethod);
    }

    /**
     * Get price value
     *
     * @return float
     */
    public function getPriceValue()
    {
        return $this->getData(self::PRICE_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setPriceValue(?float $priceValue)
    {
        return $this->setData(self::PRICE_VALUE, $priceValue);
    }
}
