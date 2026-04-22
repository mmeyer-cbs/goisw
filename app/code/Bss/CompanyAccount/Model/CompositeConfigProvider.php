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
namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Psr\Log\LoggerInterface;

/**
 * Class CompositeConfigProvider
 *
 * @package Bss\CompanyAccount\Model
 */
class CompositeConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PermissionsChecker
     */
    private $checker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CompositeConfigProvider constructor.
     *
     * @param Data $helper
     * @param PermissionsChecker $checker
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        PermissionsChecker $checker,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->checker = $checker;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $output = [];
        try {
            if ($this->helper->isEnable() &&
                $this->helper->getCustomerSession()->getSubUser()
            ) {
                $output['cant_create_address'] = $this->checker->isDenied(Permissions::ADD_VIEW_ADDRESS_BOOK);
            }
            return $output;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $output;
        }
    }
}
