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

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccountGraphQl\Model\Authorization\TokenSubUserContext;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Get sub-user by current sub-user token or company account with specific sub-id
 */
class GetSubUser extends AbstractAuthorization implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if ($field->getName() === "bssSubUser") {
            $isSubUser = $context->getExtensionAttributes()->getIsSubUser();
            if (!$isSubUser) {
                throw new GraphQlAuthorizationException(
                    __("Current sub-user isn't authorized.")
                );
            }
            $subId = $context->getExtensionAttributes()->getSubUserId();
        } else {
            $this->isAllowed($context, ['input' => $args]);
            $this->validate($args);
            $subId = $args['sub_id'];
        }

        $subUser = $this->getSubUser((int) $subId);

        if ($subUser) {
            return $subUser->with(['role', 'customer'])->getData();
        }

        return null;
    }

    /**
     * Validate params
     *
     * @param array|null $args
     * @throws GraphQlInputException
     */
    protected function validate(array $args = null)
    {
        if (!isset($args['sub_id'])) {
            throw new GraphQlInputException(
                __("Field `sub_id` is required!")
            );
        }
    }

    /**
     * Get subuser
     *
     * @param int $subId
     * @return SubUserInterface|null
     */
    protected function getSubUser(int $subId): ?SubUserInterface
    {
        $subUser = null;
        try {
            $subUser = $this->subUserRepository->getById($subId);
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Failed to load sub-user. %1", $e)
            );
        }

        return $subUser;
    }
}
