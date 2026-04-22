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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ModuleManagement
 * Get module configs
 */
class ModuleManagement implements \Bss\ForceLogin\Api\ModuleManagementInterface
{
    /**
     * Need to explode to array fields
     *
     * @var string[]
     */
    private $needConvertToArrayField = [
        "ignore_router",
        "page_id",
        "list_ignore_router"
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ModuleConfigFactory
     */
    private $moduleConfigFactory;

    /**
     * ModuleManagement constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param ModuleConfigFactory $moduleConfigFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        ModuleConfigFactory $moduleConfigFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->moduleConfigFactory = $moduleConfigFactory;
    }

    /**
     * @inheritDoc
     */
    public function getModuleConfigs($storeId = null)
    {
        $moduleConfig = $this->moduleConfigFactory->create();
        $scopeStore = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        if ($storeId !== null) {
            $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }

        try {
            $configs = $this->scopeConfig->getValue(
                "forcelogin",
                $scopeStore,
                $storeId
            );

            $configData = [];
            $this->remappingConfigData($configData, $configs);

            $moduleConfig->setData($configData);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $moduleConfig;
    }

    /**
     * Remapping the config data compatible with response data
     *
     * @param array $configData
     * @param array $configs
     * @param string|null $parentField
     */
    private function remappingConfigData(&$configData, $configs, $parentField = null)
    {
        foreach ($configs as $field => $value) {
            if (is_array($value)) {
                $this->remappingConfigData($configData, $value, $field);
                continue;
            }

            if (in_array($field, $this->needConvertToArrayField)) {
                $value = $value ? explode(",", $value) : [];
            }

            $configField = $field;
            $this->getCompatibleField($configField, $parentField, $field);
            $configData[$configField] = $value;
        }
    }

    /**
     * Get compatible field from original field
     *
     * @param string $configField
     * @param string $parentField
     * @param string $curField
     */
    private function getCompatibleField(&$configField, $parentField, $curField)
    {
        switch ($parentField) {
            case "redirect_url":
                if ($curField == "page") {
                    $configField = "redirect_to";
                }
                break;
            case "page":
                switch ($curField) {
                    case "enable":
                        $configField = "enable_cms_page";
                        break;
                    case "page_id":
                        $configField = "cms_page_ids";
                        break;
                    default:
                }
                break;
            default:
        }
    }
}
