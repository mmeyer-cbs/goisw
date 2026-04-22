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
namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Event\Observer;

/**
 * Class CustomerDashboard
 *
 * @package Bss\CompanyAccount\Observer
 */
class CustomerDashboard implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var PermissionsChecker
     */
    private $checker;

    /**
     * CustomerDashboard constructor.
     *
     * @param PermissionsChecker $checker
     */
    public function __construct(
        PermissionsChecker $checker
    ) {
        $this->checker = $checker;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $currentHandles = $layout->getUpdate()->getHandles();
        if (!in_array('customer_account_index', $currentHandles)) {
            return $this;
        }
        $checker = $this->checker->isDenied(Permissions::VIEW_ACCOUNT_DASHBOARD);
        if ($checker) {
            $layout->getUpdate()->addHandle('no_access');
        }
        return $this;
    }
}
