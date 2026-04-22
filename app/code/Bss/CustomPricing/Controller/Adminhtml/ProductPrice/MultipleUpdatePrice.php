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
namespace Bss\CustomPricing\Controller\Adminhtml\ProductPrice;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Setup\Console\InputValidationException;

/**
 * Multiple update custom price
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultipleUpdatePrice extends SaveAction
{
    protected $productPriceMethod;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Bss\CustomPricing\Helper\IndexHelper
     */
    protected $indexerHelper;

    /**
     * MultipleUpdatePrice constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param SerializerInterface $serializer
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Action\Context $context
     */
    public function __construct(
        \Bss\CustomPricing\Helper\Data $moduleHelper,
        \Psr\Log\LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        SerializerInterface $serializer,
        ProductPriceRepositoryInterface $productPriceRepository,
        PriceRuleRepositoryInterface $priceRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Action\Context $context,
        \Bss\CustomPricing\Helper\IndexHelper $indexerHelper
    ) {
        parent::__construct(
            $context,
            $resultJsonFactory,
            $productPriceRepository,
            $priceRuleRepository,
            $logger,
            $moduleHelper
        );
        $this->serializer = $serializer;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->indexerHelper = $indexerHelper;
    }

    /**
     * @inheritDoc
     */
    protected function process($postData)
    {
        $productPrices = $this->prepareProductPriceCollection($postData);
        $savedIds = [];
        foreach ($productPrices->getItems() as $cPrice) {
            $expectedPrice = $this->moduleHelper->prepareCustomPrice(
                $postData['price_type'],
                $cPrice->getOriginPrice(),
                $postData['price_value']
            );
            $cPrice->setPriceMethod($postData['price_type']);
            $cPrice->setPriceValue($postData['price_value']);
            $cPrice->setCustomPrice($expectedPrice);
            $cPrice->setShouldReindex(false);
            $this->productPriceRepository->save($cPrice);
            $savedIds[] = $cPrice->getId();
        }

        $this->indexerHelper->reindex($savedIds);
        return __("A total of %1 record(s) have been updated.", count($savedIds));
    }

    /**
     * Get list product price
     *
     * @param array $postData
     * @return \Bss\CustomPricing\Api\Data\ProductPriceSearchResultsInterface
     */
    protected function prepareProductPriceCollection($postData)
    {
        $data = $this->prepareData($postData);
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        if (is_array($data)) {
            $conditionType = 'in';
            if ($data['excluded']) {
                $conditionType = 'nin';
            }
            $searchCriteriaBuilder->addFilter(
                'id',
                $data["data"],
                $conditionType
            );
        }
        // no update [configurable, grouped, bundle product]
        $searchCriteriaBuilder->addFilter(
            'type_id',
            \Bss\CustomPricing\Model\Config\Source\ProductType::getNoNeedUpdatePType(),
            'nin'
        );
        // filter bu rule
        $searchCriteriaBuilder->addFilter(
            'rule_id',
            $postData["rule_id"],
            'eq'
        );

        return $this->productPriceRepository->getList(
            $searchCriteriaBuilder->create()
        );
    }

    /**
     * @inheritDoc
     */
    protected function validatePostData()
    {
        $ruleId = $this->_request->getPost('rule_id', null);
        if (!$ruleId) {
            throw new InputValidationException(__("Something went wrong with post data. Please try again."));
        }
        return parent::validatePostData();
    }

    /**
     * Get and prepare validated data
     *
     * @param array $postData
     * @return array|string
     * @throws InputValidationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareData($postData)
    {
        $errorMsg = __('An item needs to be selected. Select and try again.');
        if (!isset($postData['data'])) {
            throw new InputValidationException($errorMsg);
        }
        if (!is_array($postData["data"])) {
            $postData = $this->serializer->unserialize($postData["data"]);
        } else {
            $postData = $postData["data"];
        }
        $selected = null;
        $excluded = null;
        if (isset($postData["selected"])) {
            $selected = $postData["selected"];
        }
        if (isset($postData["excluded"])) {
            $excluded = $postData["excluded"];
        }
        $isExcludedIdsValid = (is_array($excluded) && !empty($excluded));
        $isSelectedIdsValid = (is_array($selected) && !empty($selected));

        if ('false' !== $excluded && !$isExcludedIdsValid && !$isSelectedIdsValid) {
            throw new InputValidationException($errorMsg);
        }

        if ($excluded == 'false') {
            return "all";
        }
        if ($excluded) {
            return [
                'excluded' => true,
                'data' => $excluded
            ];
        }
        return [
            'excluded' => false,
            'data' => $selected
        ];
    }
}
