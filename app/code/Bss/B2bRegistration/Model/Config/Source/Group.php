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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Model\Config\Source;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Customer\Model\ResourceModel\Group\Collection;

class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Collection
     */
    protected $customerGroup;

    /**
     * Group constructor.
     * @param ModuleManager $moduleManager
     * @param Collection $customerGroup
     */
    public function __construct(
        ModuleManager $moduleManager,
        Collection $customerGroup
    ) {
        $this->moduleManager = $moduleManager;
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [];

        $groups = $this->customerGroup->toOptionArray();
        foreach ($groups as $group) {
            if ($group['value'] != 0) {
                $customerGroups[] = [
                'label' => $group['label'],
                'value' => $group['value'],
                ];
            }
        }
        return $customerGroups;
    }
}
