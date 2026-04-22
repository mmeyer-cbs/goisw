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
 * @package    Bss_CompanyAccountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccountGraphQl\Model\Resolver\Extractor;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class ExtractCustomerData
 * Extract customer data from sub-user
 */
class ExtractCustomerData implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var \Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData
     */
    protected $extractCustomerData;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * ExtractCustomerData constructor.
     *
     * @param \Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData $extractCustomerData
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData $extractCustomerData,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->extractCustomerData = $extractCustomerData;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value['customer'])) {
            $customerDataModel = $value['customer']->getDataModel();
        }

        if (isset($value['customer_id'])) {
            $customerDataModel = $this->customerRepository->getById($value['customer_id']);
        }

        if (isset($customerDataModel)) {
            return $this->extractCustomerData->execute($customerDataModel);
        }

        return null;
    }
}
