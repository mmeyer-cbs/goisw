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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Block\Account;

use Magento\Customer\Model\Context;

class RegisterLink extends \Magento\Customer\Block\Account\RegisterLink
{

    /**
     * @var \Bss\ForceLogin\Helper\Data
     */
    protected $helper;

    /**
     * RegisterLink constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Bss\ForceLogin\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Customer\Model\Url $customerUrl,
        \Bss\ForceLogin\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $registration, $customerUrl, $data);
        $this->helper = $helper;
    }

    /**
     * Get string to html
     * @return string
     */
    protected function _toHtml()
    {
        $enable = $this->helper->isEnable();
        $enableRegister = $this->helper->isEnableRegister();
        if (!$this->_registration->isAllowed()
            || $this->httpContext->getValue(Context::CONTEXT_AUTH)
        ) {
            return '';
        }
        if ($enable && $enableRegister) {
            return '';
        }
        return parent::_toHtml();
    }
}
