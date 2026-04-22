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

namespace Bss\CompanyAccountGraphQl\Model\Context;

use Bss\CompanyAccountGraphQl\Model\UserContextInterface;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\GraphQl\Model\Query\ContextParametersProcessorInterface;

/**
 * Add sub-user info to context
 */
class AddSubUserInfoToContext implements ContextParametersProcessorInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
    }

    /**
     * @inheritDoc
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface
    {
        $currentUserType = $this->userContext->getUserType();
        $currentSubUserId = $this->userContext->getSubUserId();
        $contextParameters->addExtensionAttribute(
            'is_sub_user',
            $this->isSubUser($currentSubUserId, (int) $currentUserType)
        );
        $contextParameters->addExtensionAttribute('sub_user_id', $currentSubUserId);

        return $contextParameters;
    }

    /**
     * Checking if current sub-user is logged
     *
     * @param int|null $subId
     * @param int|null $type
     * @return bool
     */
    private function isSubUser(?int $subId, ?int $type): bool
    {
        return !empty($subId)
            && !empty($type)
            && $type === UserContextInterface::USER_TYPE_CUSTOMER;
    }
}
