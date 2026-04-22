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
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class AuthorizationUser
 * Authorize the admin can get the bss company account configs
 */
class AuthorizationUser extends AbstractAuthorization implements ResolverInterface
{
    const ADMIN_RESOURCE = "Bss_CompanyAccount::config_section";

    /**
     * Authorize the admin can get the bss company account configs
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     * @throws GraphQlAuthenticationException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $user = $this->getUser($context);
        if ($user &&
            $this->aclPolicy->isAllowed($user->getRole()->getId(), static::ADMIN_RESOURCE)
        ) {
            return $value[$field->getName()];
        }

        throw new GraphQlAuthenticationException(
            __("The user need to be authorized to get field `%1`", $field->getName())
        );
    }
}
