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

namespace Bss\CustomPricing\Controller\Adminhtml\PriceRules\Customer;

use Magento\Backend\App\Action;
use Bss\CustomPricing\Block\Adminhtml\PriceRule\Edit\Tab\GeneralInformation\CustomerConditions\SpecifiedGrid;

/**
 * Class Customer chooser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Chooser extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = "Bss_CustomPricing::custom_pricing";

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $request = $this->getRequest();
        switch ($request->getParam('choose_type')) {
            case "specified":
                $block = $this->_view->getLayout()->createBlock(
                    SpecifiedGrid::class,
                    'priceRules_customer_chooser_specified',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                break;
            default:
                $block = false;
                break;
        }
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
