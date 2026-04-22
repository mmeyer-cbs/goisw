<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Helper;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CheckAssignedRole
 *
 * @package Bss\CompanyAccount\Helper
 */
class CheckAssignedRole
{
    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CheckAssignedRole constructor.
     *
     * @param SubUserRepositoryInterface $subUserRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubUserRepositoryInterface $subUserRepository,
        LoggerInterface $logger
    ) {
        $this->subUserRepository = $subUserRepository;
        $this->logger = $logger;
    }

    /**
     * Check if role be assigned to sub-user
     *
     * @param int $roleId
     *
     * @return bool
     */
    public function beAssigned($roleId)
    {
        try {
            if ($this->subUserRepository->getByRole($roleId)->count() > 0) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
