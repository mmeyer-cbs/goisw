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

use Bss\CompanyAccount\Model\SubUserTokenService;
use Bss\CompanyAccountGraphQl\Model\Authorization\TokenSubUserContext;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Revoke the sub-user's token
 */
class RevokeSubUserToken implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var SubUserTokenService
     */
    protected $tokenService;

    /**
     * RevokeSubUserToken constructor.
     *
     * @param SubUserTokenService $tokenService
     */
    public function __construct(
        SubUserTokenService $tokenService
    ) {
        $this->tokenService = $tokenService;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$context->getExtensionAttributes()->getIsSubUser()) {
            throw new GraphQlAuthorizationException(
                __("The current sub-user isn't authorized.")
            );
        }

        return [
            'result' => $this->tokenService->revokeSubUserAccessToken(
                $context->getExtensionAttributes()->getSubUserId()
            )
        ];
    }
}
