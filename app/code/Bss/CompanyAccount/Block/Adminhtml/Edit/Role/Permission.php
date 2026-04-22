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

namespace Bss\CompanyAccount\Block\Adminhtml\Edit\Role;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Block\Role\Edit;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\Config\Source\Permissions as PermissionSource;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Bss\CompanyAccount\Helper\HelperData;

/**
 * Class Permission
 *
 * @package Bss\CompanyAccount\Block\Adminhtml\Edit\Role
 */
class Permission extends \Magento\Backend\Block\Template
{
    protected $_template = "Bss_CompanyAccount::roles/permissions.phtml";

    /**
     * @var PermissionSource
     */
    private $permissionSource;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * Permission constructor.
     *
     * @param ProductMetadataInterface $magentoVersion
     * @param Context $context
     * @param PermissionSource $permissionSource
     * @param Json $serializer
     * @param SubRoleRepositoryInterface $roleRepository
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        HelperData                                   $helperData,
        \Magento\Backend\Block\Template\Context      $context,
        PermissionSource                             $permissionSource,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        SubRoleRepositoryInterface                   $roleRepository,
        Data                                         $helper,
        array                                        $data = []
    ) {
        $this->helperData = $helperData;
        $this->permissionSource = $permissionSource;
        $this->helper = $helper;
        $this->serializer = $serializer;
        $this->roleRepository = $roleRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get rules data
     *
     * @return array
     */
    public function getDataRules(array $selectedResources = [], $magentoHigherV244 = null)
    {
        return $this->permissionSource->mappedDataArray($selectedResources, $magentoHigherV244);
    }

    /**
     * Get selected rule for tree js
     *
     * @return false|string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSelectedRules()
    {
        $selectedRules = $this->getData('selected_rules');
        if (empty($selectedRules)) {
            $selectedRules = $this->helper->getDataHelper()->getCoreSession()->getRulesFormData();
            if (null === $selectedRules) {
                $roleId = $this->getRequest()->getParam('role_id');
                $role = $this->roleRepository->getById($roleId);
                $selectedRules = $role->getRoleType();
            }
        }
        if (null === $selectedRules) {
            return [];
        }
        if (!is_array($selectedRules)) {
            $selectedRules = explode(',', $selectedRules);
        }
        $this->setData('selected_rules', $selectedRules);
        return $selectedRules;
    }

    /**
     * Get serializer object
     *
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Check magento >= 2.4.4 or not
     *
     * @return bool|int
     */
    public function checkMagentoVersionHigherV244()
    {
        return $this->helperData->checkMagentoVersionHigherV244();
    }
}
