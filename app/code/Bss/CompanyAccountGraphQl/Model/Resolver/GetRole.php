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

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get company account role by id
 */
class GetRole extends AbstractAuthorization implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context, ['input' => $args]);
        try {
            $this->validate($args);
            $role = $this->subModelRepository->getById($args['role_id']);

            if ($role->getRoleId()) {
                return $role->getData();
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Failed to get role. %1", $e)
            );

            throw new GraphQlNoSuchEntityException(
                __("Could not %1 the %2. Please try again!", 'get', 'role')
            );
        }
    }

    /**
     * Validate params
     *
     * @param array|null $args
     * @throws GraphQlInputException
     */
    protected function validate(array $args = null)
    {
        if (!isset($args['role_id']) || (!$args['role_id'] && $args['role_id'] !== 0)) {
            throw new GraphQlInputException(
                __("Role id must be defined!")
            );
        }
    }
}
