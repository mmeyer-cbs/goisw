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

namespace Bss\CompanyAccountGraphQl\Plugin;

use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccountGraphQl\Exception\GraphQlSubUserAccessException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Can customer view downloadable products
 */
class CustomerDownloadableProducts extends SubUserActionAbstract
{
    const PERMISSION = Permissions::VIEW_DOWNLOADABLE_PRODUCT;

    /**
     * Check sub-user permission
     *
     * @param $subject
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlSubUserAccessException|\Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function beforeResolve(
        $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->isAllowed($context);

        return [$field, $context, $info, $value, $args];
    }
}
