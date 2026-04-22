<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_B2bRegistrationGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\B2bRegistrationGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GetModuleConfig
 * Get all module config backend
 */
class GetModuleConfig implements ResolverInterface
{
    /**
     * @var string[]
     */
    private $needProcessToArrayFields = [
        "admin_notification_enable",
        "admin_recipeints"
    ];

    const STORE_CONFIG_XML_PATH = "b2b";

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * GetModuleConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['store_view'])) {
            throw new GraphQlInputException(__("The \"store_view\" param is not defined."));
        }

        return $this->getConfig($args["store_view"]);
    }

    /**
     * Get bss b2b registration store config
     *
     * @param int $storeId
     * @return mixed
     */
    private function getConfig($storeId)
    {
        $scopeType = $storeId != 0 ? ScopeInterface::SCOPE_STORES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        $configs = $this->scopeConfig->getValue(
            self::STORE_CONFIG_XML_PATH,
            $scopeType,
            $storeId
        );

        return $this->mappingConfigData($configs);
    }

    /**
     * Mapping to flat data
     *
     * @param array $configs
     * @return array
     */
    private function mappingConfigData($configs)
    {
        $configsData = [];
        foreach ($configs as $key => $config) {
            if (is_array($config)) {
                $configsData += $this->mappingConfigData($config);
                continue;
            }

            if (in_array($key, $this->needProcessToArrayFields)) {
                $config = explode(",", $config);
            }

            $configsData[$key] = $config;
        }

        return $configsData;
    }
}
