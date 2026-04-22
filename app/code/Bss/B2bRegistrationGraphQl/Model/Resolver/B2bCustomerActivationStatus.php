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
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute as B2bCustomerAttributeSource;

/**
 * Class B2bCustomerActivationStatus
 * Add b2b activation status to customer query
 */
class B2bCustomerActivationStatus implements ResolverInterface
{
    /**
     * @var B2bCustomerAttributeSource
     */
    private $customerAttributeSource;

    /**
     * B2bCustomerActivationStatus constructor.
     *
     * @param B2bCustomerAttributeSource $customerAttributeSource
     */
    public function __construct(
        B2bCustomerAttributeSource $customerAttributeSource
    ) {
        $this->customerAttributeSource = $customerAttributeSource;
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
        if (!isset($value['model'])) {
            throw new GraphQlNoSuchEntityException(__("\"model\" value should be specified"));
        }

        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $value["model"];
        $b2bActivationStatus = $customer->getCustomAttribute("b2b_activasion_status");

        if (!$b2bActivationStatus) {
            return null;
        }

        $b2bActivationStatusOptions = $this->customerAttributeSource->getAllOptions();
        $b2bActivationStatusOptions = array_filter(
            $b2bActivationStatusOptions,
            function ($option) use ($b2bActivationStatus) {
                if ($option["value"] == $b2bActivationStatus->getValue()) {
                    return $option;
                }

                return null;
            }
        );

        if ($b2bActivationStatusOptions) {
            foreach ($b2bActivationStatusOptions as $option) {
                return $option;
            }
        }

        return null;
    }
}
