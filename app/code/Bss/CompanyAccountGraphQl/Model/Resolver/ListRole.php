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
namespace Bss\CompanyAccountGraphQl\Model\Resolver;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get list role were created by current company account
 */
class ListRole implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var SubRoleRepositoryInterface
     */
    protected $subRoleRepository;

    /**
     * GetListRoles constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param SubRoleRepositoryInterface $subRoleRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        SubRoleRepositoryInterface $subRoleRepository
    ) {
        $this->criteriaBuilder = $criteriaBuilder;
        $this->subRoleRepository = $subRoleRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            return [];
        }

        $companyAccountId = $value['model']->getId();
        $subRoles = $this->subRoleRepository->getListByCustomer((int) $companyAccountId);
        $result = [];

        foreach ($subRoles->getItems() as $item) {
            $result[] = $item->getData();
        }

        return $result;
    }
}
