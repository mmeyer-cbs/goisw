<?php
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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Ui\Component;

use Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Module\Manager;

class DataProvider
{
    /**
     * @var Customerattribute
     */
    protected $customerAttribute;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var B2BRegistrationIntegrationHelper
     */
    protected $b2bHelper;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * DataProvider constructor.
     * @param Config $config
     * @param CustomerSetupFactory $customerSetupFactory
     * @param B2BRegistrationIntegrationHelper $b2bHelper
     * @param Attribute $eavAttribute
     * @param Manager $moduleManager
     */
    public function __construct(
        Config $config,
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $setup,
        B2BRegistrationIntegrationHelper $b2bHelper,
        Attribute $eavAttribute,
        Manager $moduleManager
    ) {
        $this->config = $config;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
        $this->b2bHelper = $b2bHelper;
        $this->eavAttribute = $eavAttribute;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Customer\Ui\Component\DataProvider $subject
     * @param array $proceed
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundGetData($subject, $proceed)
    {
        $entityTypeId = $this->config->getEntityType(Customer::ENTITY)->getEntityTypeId();
        $attributeId = $this->eavAttribute->getIdByCode(Customer::ENTITY, 'b2b_activasion_status');
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
        if ($attributeId) {
            if (!$this->moduleManager->isEnabled('Bss_B2bRegistration')) {
                $customerSetup->updateAttribute($entityTypeId, $attributeId, 'is_used_in_grid', false);
            } else {
                $customerSetup->updateAttribute($entityTypeId, $attributeId, 'is_used_in_grid', true);
                $customerSetup->updateAttribute($entityTypeId, $attributeId, 'is_visible_in_grid', true);
                $customerSetup->updateAttribute($entityTypeId, $attributeId, 'is_filterable_in_grid', true);
            }
        }
        $data = $proceed();
        foreach ($data['items'] as &$item) {
            $item_keys = array_keys($item);
            foreach ($item_keys as $key) {
                $attributeCodeCustom = $this->config->getAttribute('customer', $key);
                if ($attributeCodeCustom) {
                    if ($attributeCodeCustom->getFrontendInput() == 'file') {
                        $item[$key] = $this->getFileName($item[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param string $filename
     * @return mixed
     */
    protected function getFileName($filename)
    {
        if ($filename && strpos($filename, "/") !==false) {
            $nameArr = explode("/", $filename);
            return end($nameArr);
        }
        return $filename;
    }
}
