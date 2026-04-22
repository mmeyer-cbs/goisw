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
namespace Bss\B2bRegistration\Plugin;

use Bss\B2bRegistration\Helper\Data;

class Registration
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * EmailNotification constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Check
     *
     * @param \Magento\Customer\Model\Registration $subject
     * @param bool $result
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowed(\Magento\Customer\Model\Registration $subject, $result)
    {
        $enable = $this->helper->isEnable();
        $disableRegularForm = $this->helper->disableRegularForm();
        if ($enable && $disableRegularForm) {
            $result = false;
            return $result;
        }
        return $result;
    }
}
