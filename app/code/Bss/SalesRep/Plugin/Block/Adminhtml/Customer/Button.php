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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Plugin\Block\Adminhtml\Customer;

use Magento\Customer\Block\Adminhtml\Edit\InvalidateTokenButton;

/**
 * Class Button
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Customer
 */
class Button extends BackButton
{
    /**
     * Unset key aclResource
     *
     * @param InvalidateTokenButton $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetButtonData($subject, $result)
    {
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            unset($result['aclResource']);
            return $result;
        }
        return $result;
    }
}
