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
 * @package    Bss_CustomPricingGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricingGraphQl\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GetModuleConfig
 * Get module configs
 */
class GetModuleConfig implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    const STORE_CONFIG_XML_PATH = "bss_custom_pricing";

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

        $scopeType = $args['store_view'] != 0 ? ScopeInterface::SCOPE_STORES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $configs = $this->scopeConfig->getValue(
            self::STORE_CONFIG_XML_PATH,
            $scopeType,
            $args['store_view']
        );
        $flatData = [];
        $this->getFlatConfigData($flatData, $configs);

        return $flatData;
    }

    /**
     * Get flat configs data
     *
     * @param array $flatData
     * @param array $configs
     */
    private function getFlatConfigData(&$flatData, $configs)
    {
        foreach ($configs as $field => $configValue) {
            if (is_array($configValue)) {
                $this->getFlatConfigData($flatData, $configValue);
                continue;
            }

            $flatData[$field] = $configValue;
        }
    }
}
