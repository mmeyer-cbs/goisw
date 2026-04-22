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

namespace Bss\CompanyAccount\Block\Role;

use Bss\CompanyAccount\Api\Data\SubRoleInterface;
use Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory;
use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Block\Adminhtml\Edit\Role\Permission;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zend\Stdlib\Parameters;
use Bss\CompanyAccount\Helper\HelperData;

class Edit extends Template
{
    /**
     * @var SubRoleInterface
     */
    protected $role = null;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var SubRoleInterfaceFactory
     */
    private $roleFactory;

    /**
     * @var Permissions
     */
    private $permissions;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * Edit constructor.
     *
     * @param HelperData $helperData
     * @param Context $context
     * @param Session $customerSession
     * @param Permission $permission
     * @param SubRoleInterfaceFactory $roleFactory
     * @param SubRoleRepositoryInterface $roleRepository
     * @param Data $helper
     * @param SerializerInterface $serializer
     * @param Permissions $permissions
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        HelperData                 $helperData,
        Template\Context           $context,
        Session                    $customerSession,
        Permission                 $permission,
        SubRoleInterfaceFactory    $roleFactory,
        SubRoleRepositoryInterface $roleRepository,
        Data                       $helper,
        SerializerInterface        $serializer,
        Permissions                $permissions,
        array                      $data = []
    ) {
        $this->helperData = $helperData;
        $this->roleRepository = $roleRepository;
        $this->customerSession = $customerSession;
        $this->roleFactory = $roleFactory;
        $this->permissions = $permissions;
        $this->helper = $helper;
        $this->permission = $permission;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Convert amount currency
     *
     * @param float $amount
     * @return float|string
     * @throws NoSuchEntityException
     */
    public function convertCurrency($amount)
    {
        if (empty($amount)) {
            return '';
        }
        return ($this->helper->convertCurrency($amount));
    }

    /**
     * Get serializer object
     *
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get list rules
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataRules($magentoHigherV244 = null)
    {
        return $this->permission->getDataRules($this->getSelectedRules(), $magentoHigherV244);
    }

    /**
     * Get permissions of role
     *
     * @return false|string[]
     * @throws NoSuchEntityException
     */
    public function getSelectedRules()
    {
        return $this->permission->getSelectedRules();
    }

    /**
     * Prepare render layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->initRole();
        $this->pageConfig->getTitle()->set($this->getTitle());
    }

    /**
     * Initialize role object
     *
     * @return void
     */
    public function initRole()
    {
        /** @var Parameters $oldRoleData */
        $oldRoleData = $this->helper->getDataHelper()->getCoreSession()->getRoleFormData();
        if ($oldRoleData) {
            $this->role = $this->roleFactory->create();
            $this->role->setRoleName($oldRoleData->get('role_name'));
            if (is_array($oldRoleData->get('role_type'))) {
                $this->role->setRoleType(implode(',', $oldRoleData->get('role_type')));
            } else {
                $this->role->setRoleType($oldRoleData->get('role_type') ?? "");
            }
            $this->role->setMaxOrderAmount($oldRoleData->get('max_order_amount'));
            $this->role->setMaxOrderPerDay($oldRoleData->get('order_per_day'));
            $this->helper->getDataHelper()->getCoreSession()->unsRoleFormData();
        } elseif ($roleId = $this->getRequest()->getParam('role_id')) {
            try {
                $this->role = $this->roleRepository->getById($roleId);
                if ($this->getRole()->getCompanyAccount() != $this->customerSession->getCustomerId()) {
                    $this->role = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->role = null;
            }
        }
        if ($this->role === null) {
            $this->role = $this->roleFactory->create();
        }
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        if (!$this->getRole()->getRoleId()) {
            $title = __('Add New Role');
        } else {
            $title = __('Edit %1', $this->getRole()->getRoleName());
        }
        return $title;
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'companyaccount/role/formPost',
            ['_secure' => true, 'role_id' => $this->getRole()->getRoleId()]
        );
    }

    /**
     * Get role
     *
     * @return SubRoleInterface
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get previous url
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        return $this->getUrl('companyaccount/role/');
    }

    /**
     * Get permissions html text
     *
     * @return string
     */
    public function getPermissionOptions()
    {
        $permissions = $this->permissions->toOptionArray();
        return $this->getSelectOptions($permissions);
    }

    /**
     * Get array of permission
     *
     * @return array
     */
    protected function toRolePermissionArray()
    {
        if ($this->role->getRoleType()) {
            return explode(',', $this->role->getRoleType());
        }
        return [];
    }

    /**
     * Get content of select option
     *
     * @param array $options
     * @return string
     */
    protected function getSelectOptions($options)
    {
        $htmlContent = '';
        $rolePermissions = $this->toRolePermissionArray();
        foreach ($options as $option) {
            $htmlContent .= '<option value="' . $option['value'] . '" ';
            if (in_array($option['value'], $rolePermissions)) {
                $htmlContent .= 'selected';
            }
            $htmlContent .= '>' . $option['label'] . '</option>';
        }
        return $htmlContent;
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
