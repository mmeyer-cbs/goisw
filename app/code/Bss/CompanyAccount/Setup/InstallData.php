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
namespace Bss\CompanyAccount\Setup;

use Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Bss\CompanyAccount\Api\Data\SubRoleInterface as Role;
use Magento\Customer\Model\Customer;

/**
 * Class InstallData that prepare data when install
 *
 * Bss\CompanyAccount\Setup
 */
class InstallData implements InstallDataInterface
{
    public const ADMIN = 0;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var SubRoleInterfaceFactory
     */
    private $subRoleFactory;

    /**
     * InstallData constructor.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SubRoleInterfaceFactory $subRoleFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        SubRoleInterfaceFactory $subRoleFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->subRoleFactory = $subRoleFactory;
    }

    /**
     * Edit new is company account attribute
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
            Role::ID => 0,
            Role::NAME => 'admin',
            Role::TYPE => self::ADMIN,
            Role::CUSTOMER_ID => null
        ];

        $isExist = false;
        $roles = $this->subRoleFactory->create()->getCollection();
        foreach ($roles as $role) {
            if ($role->getRoleType() == self::ADMIN) {
                $isExist = true;
                break;
            }
        }
        if (!$isExist) {
            $setup->getConnection()->insertForce($setup->getTable('bss_sub_role'), $data);
        }

        $setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'bss_is_company_account',
            [
                'label' => 'Is Company Account',
                'input' => 'boolean',
                'required' => false,
                'sort_order' => 120,
                'visible' => false,
                'user_defined' => true,
                'position' => 500,
                'system' => false,
                'is_used_in_grid' => true,
                'type' => 'int',
                'default' => 0,
                'source' => Boolean::class
            ]
        );

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(
                Customer::ENTITY, 'bss_is_company_account'
            )->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer']
                ]
            );
        $attribute->save();

        $setup->endSetup();
    }
}
