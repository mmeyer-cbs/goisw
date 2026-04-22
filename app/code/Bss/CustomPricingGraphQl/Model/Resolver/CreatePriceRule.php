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
 * @package    Bss_CustomPricingGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricingGraphQl\Model\Resolver;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Helper\PriceRuleSave;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Bss\CustomPricing\Model\PriceRuleFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CreatePriceRule
 * Create price rule with flat product and customer condition
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePriceRule extends AbstractPriceRuleResolver implements ResolverInterface
{
    const ADMIN_RESOURCE = "Bss_CustomPricing::custom_pricing_new_rule";

    /**
     * The boolean values
     *
     * @var int[]
     */
    private $booleanValues = [0, 1];

    /**
     * @var PriceRuleFactory
     */
    private $priceRuleFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceRuleSave
     */
    private $priceRuleSave;

    /**
     * CreatePriceRule constructor.
     *
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param PriceRuleFactory $priceRuleFactory
     * @param StoreManagerInterface $storeManager
     * @param PriceRuleSave $priceRuleSave
     */
    public function __construct(
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface $logger,
        PriceRuleFactory $priceRuleFactory,
        StoreManagerInterface $storeManager,
        PriceRuleSave $priceRuleSave
    ) {
        $this->priceRuleFactory = $priceRuleFactory;
        $this->storeManager = $storeManager;
        $this->priceRuleSave = $priceRuleSave;
        parent::__construct($priceRuleRepository, $searchCriteriaBuilder, $logger, $userFactory, $aclPolicy);
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->isAllowed($context);
        if (!isset($args["input"])) {
            throw new GraphQlInputException(__("The \"input\" should be defined."));
        }
        $this->validate($args);

        try {
            $priceRule = $this->initPriceRule($args["input"]);
            $priceRule = $this->priceRuleRepository->save($priceRule);

            $this->priceRuleSave->saveProductPrice($priceRule);
            $this->priceRuleSave->saveAppliedCustomers($priceRule);
            $this->priceRuleSave->markInvalidateCache();

            return $priceRule->reloadRelations($priceRule);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__("Something went wrong. Please review the log!"));
        }
    }

    /**
     * Validate data
     *
     * @param array $data
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    public function validate($data)
    {
        parent::validate($data);
        $booleanFields = ['status', 'is_not_logged_rule'];
        foreach ($booleanFields as $field) {
            if (!in_array($data["input"][$field], $this->booleanValues)) {
                throw new GraphQlInputException(__('The ' . $field . '\'s field should be 0 (disable) or 1 (enable).'));
            }
        }

        // Validate website is exists
        $this->storeManager->getWebsite($data['input']['website_id']);
    }

    /**
     * Price rule initialization
     *
     * @param array $data
     * @return \Bss\CustomPricing\Model\PriceRule
     */
    protected function initPriceRule($data)
    {
        try {
            $priceRule = $this->priceRuleRepository
                ->getById($data['id'] ?? null);
        } catch (\Exception $e) {
            /** @var \Bss\CustomPricing\Model\PriceRule $priceRule */
            $priceRule = $this->priceRuleFactory->create();
        }

        $priceRule->setName($data["name"]);
        $priceRule->setStatus($data["status"] ?? 0);
        $priceRule->setWebsiteId($data["website_id"]);
        $priceRule->setIsNotLoggedRule($data["is_not_logged_rule"] ?? 0);
        $priceRule->setDescription($data["description"] ?? null);
        $priceRule->setDefaultPriceValue($data['default_price_value'] ?? null);
        $priceRule->setDefaultPriceMethod($data['default_price_type'] ?? 1);
        $priceRule->loadConditionsData($data);
        $priceRule->with($this->getRelationFields());

        return $priceRule;
    }
}
