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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Block\Adminhtml\PriceRule\Edit\Tab;

use Bss\CustomPricing\Controller\RegistryConstants;
use Bss\CustomPricing\Model\PriceRule;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class CanVisiable for price rule
 */
class ProductPrice extends \Magento\Ui\Component\Form\Fieldset implements ComponentVisibilityInterface
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * CanVisiable constructor.
     *
     * @param ContextInterface $context
     * @param Registry $coreRegistry
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Registry $coreRegistry,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @inheritDoc
     */
    public function isComponentVisible(): bool
    {
        /** @var PriceRule $rule */
        $rule = $this->coreRegistry->registry(RegistryConstants::CURRENT_PRICE_RULE);
        if ($rule->getId()) {
            return true;
        }
        return false;
    }
}
