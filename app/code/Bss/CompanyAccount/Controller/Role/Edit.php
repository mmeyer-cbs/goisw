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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Controller\Role;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\App\Action\Context;
use Bss\CompanyAccount\Helper\HelperData;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Class Edit
 *
 * @package Bss\CompanyAccount\Block\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Edit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * Edit constructor.
     *
     * @param HelperData $helperData
     * @param ForwardFactory $resultForwardFactory
     * @param Context $context
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        HelperData                                          $helperData,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Context                                             $context,
        \Bss\CompanyAccount\Helper\PermissionsChecker $permissionsChecker
    ) {
        $this->helperData = $helperData;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Get Edit new sub-user form
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        if (!$this->permissionsChecker->check(Permissions::MANAGE_SUB_USER_AND_ROLES)) {
            if ($this->helperData->checkMagentoVersionHigherV244()) {
                return $resultForward->forward('formv244');
            }
            return $resultForward->forward('form');
        }
        return $resultForward->forward('index');
    }
}
