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
 * @package    BSS_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Block\Form\Login;

class Info
{

    /**
     * Data
     * @var \Bss\ForceLogin\Helper\Data
     */
    protected $helper;

    /**
     * Info constructor.
     * @param \Bss\ForceLogin\Helper\Data $helper
     */
    public function __construct(\Bss\ForceLogin\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\Block\Form\Login\Info $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param $result
     * @return string
     */
    public function afterGetTemplate(\Magento\Customer\Block\Form\Login\Info $subject, $result)
    {
        $enable = $this->helper->isEnable();
        $enableRegister = $this->helper->isEnableRegister();
        $enableB2bRegistration = $this->helper->isEnableB2bRegistration();
        if ($enable && $enableRegister && $enableB2bRegistration) {
            return $result;
        }
        if ($enable && $enableRegister) {
            return "";
        }

        return $result;
    }
}
