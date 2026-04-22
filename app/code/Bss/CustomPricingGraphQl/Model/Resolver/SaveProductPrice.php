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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricingGraphQl\Model\Resolver;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Bss\CustomPricing\Helper\Data;
use Bss\CustomPricing\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Authorization\PolicyInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Setup\Exception;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class SaveProductPrice
 */
class SaveProductPrice extends AbstractPriceRuleResolver implements ResolverInterface
{
    const ADMIN_RESOURCE = "Bss_CustomPricing::custom_pricing_update_product_price";

    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    private $customPricingHelper;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * SaveProductPrice constructor.
     *
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     * @param UserFactory $userFactory
     * @param PolicyInterface $aclPolicy
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param Data $customPricingHelper
     * @param ProductResource $productResource
     */
    public function __construct(
        PriceRuleRepositoryInterface                     $priceRuleRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder     $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface                         $logger,
        \Magento\User\Model\UserFactory                  $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        ProductPriceRepositoryInterface                  $productPriceRepository,
        \Bss\CustomPricing\Helper\Data                   $customPricingHelper,
        ProductResource                                  $productResource
    ) {
        parent::__construct($priceRuleRepository, $searchCriteriaBuilder, $logger, $userFactory, $aclPolicy);
        $this->productPriceRepository = $productPriceRepository;
        $this->customPricingHelper = $customPricingHelper;
        $this->productResource = $productResource;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        $this->isAllowed($context);
        $this->validate($args);

        $result = [];
        foreach ($args['input'] as $priceData) {
            try {
                $productPrice = $this->productPriceRepository->getById($priceData['id']);
            } catch (Exception $e) {
                throw new GraphQlInputException(__("We could't find id product price"));
            } catch (Throwable $error) {
                throw new GraphQlInputException(
                    __(
                        'The product price rule ID wasn\'t found. Verify the ID and try again.'
                    )
                );
            }
            $this->updateProductPrice($productPrice, $priceData);
            $result[] = $productPrice;
        }

        return $result;
    }

    /**
     * Update product price
     *
     * @param \Bss\CustomPricing\Api\Data\ProductPriceInterface $productPrice
     * @param array $priceData
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function updateProductPrice($productPrice, $priceData)
    {
        if ($productPrice->getTypeId() === \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            // Skip update dynamic bundle product
            if (!$this->productResource->isFixedPriceType($productPrice->getProductId())) {
                return;
            }
        }
        $priceValue = $priceData['custom_price'];
        $priceData['custom_price'] = $this->customPricingHelper->prepareCustomPrice(
            $priceData['price_type'],
            $productPrice->getOriginPrice(),
            $priceData['custom_price']
        );
        $productPrice->setPriceMethod($priceData['price_type']);
        $productPrice->setPriceValue($priceValue);
        $productPrice->setCustomPrice($priceData['custom_price']);
        $this->productPriceRepository->save($productPrice);
    }
}
