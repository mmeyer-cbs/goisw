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
namespace Bss\CompanyAccount\Observer\QuoteExtension;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Bss\CompanyAccount\Helper\PermissionsChecker;

/**
 * Class Add
 *
 * @package Bss\CompanyAccount\Observer\QuoteExtension
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Add implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * Add constructor.
     * @param RequestInterface $request
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        RequestInterface $request,
        PermissionsChecker $permissionsChecker
    ) {
        $this->request = $request;
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Set Sub-User Id
     *
     * @param Observer $observer
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $subUserId = $this->permissionsChecker->getSubId();
        if ($subUserId) {
            $action = $this->request->getActionName();
            $notAction = ['edit', 'sendCustomer', 'rejected'];
            $listModule = ['sales', 'admin'];
            $module = $this->request->getModuleName();
            if (!in_array($action, $notAction) && !in_array($module, $listModule)) {
                $observer->getDataObject()->setSubId($subUserId);
            }
        }
    }
}
