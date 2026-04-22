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
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

/**
 * Class GetSalesRepUser
 * Get salesRep user information
 */
class GetSalesRepUser implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * GetSalesRepUser constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        $this->userFactory = $userFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['bss_sales_representative']) || !$value['bss_sales_representative']) {
            return null;
        }

        $user = $this->userFactory->create();

        try {
            $user->load($value['bss_sales_representative']);
        } catch (\Exception $e) {
            return null;
        }

        return $user;
    }
}
