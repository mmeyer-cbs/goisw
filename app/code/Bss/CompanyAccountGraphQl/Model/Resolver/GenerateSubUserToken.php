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
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Generate sub-user token
 */
class GenerateSubUserToken implements ResolverInterface
{
    /**
     * @var SubUserTokenService
     */
    protected $tokenService;

    /**
     * GenerateSubUserToken constructor.
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
        $this->validate($args);
        try {
            $token = $this->tokenService->createSubUserAccessToken(
                $args['email'],
                $args['password']
            );

            return ['token' => $token];
        } catch (\Exception $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        }
    }

    /**
     * Validate params
     *
     * @param array|null $args
     * @throws GraphQlInputException
     */
    public function validate(array $args = null)
    {
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (empty($args['password'])) {
            throw new GraphQlInputException(__('Specify the "password" value.'));
        }
    }
}
