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
 * @package    Bss_StoreCreditGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCreditGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class Credit
 *
 * @package Bss\StoreCreditGraphQl\Model\Resolver
 */
class Credit implements ResolverInterface
{
    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    protected $storeCreditRepository;


    /**
     * Credit constructor.
     *
     * @param \Bss\StoreCredit\Api\StoreCreditRepositoryInterface $storeCreditRepository
     */
    public function __construct(
        \Bss\StoreCredit\Api\StoreCreditRepositoryInterface $storeCreditRepository
    ) {
        $this->storeCreditRepository = $storeCreditRepository;
    }

    /**
     * Get Store Credit current of customer
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $result = [];
        $customerId = $context->getUserId();
        $websiteId = $context->getExtensionAttributes()->getStore()->getWebsiteId();
        $credit = $this->storeCreditRepository->get($customerId, $websiteId);
        if ($credit && $credit->getId()) {
            $result = $credit->getData();
        }
        return $result;
    }
}
