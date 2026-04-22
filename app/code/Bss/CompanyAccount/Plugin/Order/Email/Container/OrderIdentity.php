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
namespace Bss\CompanyAccount\Plugin\Order\Email\Container;

use Bss\CompanyAccount\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class OrderIdentity
 *
 * @package Bss\CompanyAccount\Plugin\Order\Email\Container
 */
class OrderIdentity
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderIdentity constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * If take order is sub-user
     *
     * @param Object $subject
     * @param array|bool $result
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetEmailCopyTo(
        $subject,
        $result
    ) {
        try {
            if ($this->helper->isEnable()) {
                /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
                $subUser = $this->customerSession->getSubUser();
                if (!$subUser) {
                    $subUser = $this->registry->registry('bss_is_send_mail_to_sub_user');
                }
                if ($subUser) {
                    $result[] = $subUser->getSubEmail();
                }
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $result;
        }
    }
}
