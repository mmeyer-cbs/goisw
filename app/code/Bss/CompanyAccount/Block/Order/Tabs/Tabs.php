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

namespace Bss\CompanyAccount\Block\Order\Tabs;

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Tabs Sales Order History
 *
 * @api
 * @since 100.0.2
 */
class Tabs extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PermissionsChecker
     */
    protected $permissionChecker;

    /**
     * @param Context $context
     * @param Data $helper
     * @param PermissionsChecker $permissionChecker
     * @param array $data
     */
    public function __construct(
        Template\Context   $context,
        Data               $helper,
        PermissionsChecker $permissionChecker,
        array              $data = []
    ) {
        $this->helper = $helper;
        $this->permissionChecker = $permissionChecker;
        parent::__construct($context, $data);
    }

    /**
     * Check if customer is sub user
     *
     * @return bool
     */
    public function isCompanyAccount()
    {
        return $this->helper->isCompanyAccount();
    }

    /**
     * Check permission approve order waiting
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canApproveOrderWaitingOnly(): bool
    {
        if ($this->permissionChecker->isDenied(Permissions::PLACE_ORDER)
            && !$this->permissionChecker->isDenied(Permissions::APPROVE_ORDER_WAITING)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return tab url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('sales/order/history', ['tab' => 'order']);
    }

    /**
     * @return bool
     */
    public function isTabActive()
    {
        $name = $this->getRequest()->getParam('tab');
        return $name == 'order' || $name == '';
    }

    /**
     * Return which tab opening
     *
     * @return false|int
     */
    public function getTab()
    {
        $name = $this->getRequest()->getParam('tab');
        switch ($name) {
            case 'waiting':
                return 1;
            case 'approve':
                return 2;
            case 'reject':
                return 3;
            default:
                return false;
        }
    }
}
