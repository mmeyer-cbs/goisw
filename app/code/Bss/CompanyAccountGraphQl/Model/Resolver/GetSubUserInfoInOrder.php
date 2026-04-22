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

use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get sub-user info related to the order
 */
class GetSubUserInfoInOrder implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var SubUserOrderRepositoryInterface
     */
    protected $subUserOrderRepository;

    /**
     * GetSubUserInfoInOrder constructor.
     *
     * @param SubUserOrderRepositoryInterface $subUserOrderRepository
     */
    public function __construct(
        SubUserOrderRepositoryInterface $subUserOrderRepository
    ) {
        $this->subUserOrderRepository = $subUserOrderRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value['id'])) {
            $orderId = $value['id'];
        }

        if (isset($value['model'])) {
            $orderId = $value['model']->getEntityId();
        }

        if (!isset($orderId)) {
            return null;
        }

        $userOrder = $this->subUserOrderRepository->getByOrderId($orderId);

        if (!$userOrder) {
            return null;
        }

        if ($userOrder->getSubUserInfo()) {
            $subUserData = $userOrder->getSubUserInfo();
            $subUserData['role']['role_name'] = $subUserData['role_name'];

            return $subUserData;
        }

        return null;
    }
}
