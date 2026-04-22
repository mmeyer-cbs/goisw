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
namespace Bss\CompanyAccount\Controller\SubUser;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Class Edit
 *
 * @package Bss\CompanyAccount\Block\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Create extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Edit constructor.
     *
     * @param ForwardFactory $resultForwardFactory
     * @param Context $context
     * @param PermissionsChecker $permissionsChecker
     * @param Session $session
     */
    public function __construct(
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Context $context,
        \Bss\CompanyAccount\Helper\PermissionsChecker $permissionsChecker,
        \Magento\Customer\Model\Session $session
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->permissionsChecker = $permissionsChecker;
        $this->session = $session;
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
        $flag = false;
        if ($this->session->isLoggedIn()) {
            if ($this->session->getCustomer()->getData('bss_is_company_account')) {
                $flag = true;
            }
        }
        if ($flag && !$this->permissionsChecker->check(Permissions::MANAGE_SUB_USER_AND_ROLES)) {
            return $resultForward->forward('form');
        }
        return $resultForward->forward('index');
    }
}
