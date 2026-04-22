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
namespace Bss\CustomPricing\Ui\Component\Listing\AppliedCustomers;

use Bss\CustomPricing\Model\AppliedCustomers;
use Bss\CustomPricing\Model\ResourceModel\AppliedCustomers\CollectionFactory;

/**
 * Custom DataProvider for customer addresses listing
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

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
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
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
            $this->getCollection()->addFieldToFilter(
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
                    AppliedCustomers::ID,
                    AppliedCustomers::CUSTOMER_FIRST_NAME,
                    AppliedCustomers::CUSTOMER_LAST_NAME,
                    AppliedCustomers::CUSTOMER_ID,
                    AppliedCustomers::APPLIED_RULE
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
