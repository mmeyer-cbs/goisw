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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\B2bRegistration\Plugin\Block\Widget;

use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Model\CustomerB2b;

class Name
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CustomerB2b
     */
    protected $customerSession;

    /**
     * Construct function
     *
     * @param Data $helper
     * @param CustomerB2b $customerSession
     */
    public function __construct(
        Data $helper,
        CustomerB2b $customerSession
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Can show prefix
     *
     * @param \Magento\Customer\Block\Widget\Name $subject
     * @param $result
     * @return mixed|string
     */
    public function afterShowPrefix(\Magento\Customer\Block\Widget\Name $subject , $result)
    {
        if ($this->customerSession->isB2bAccount()) {
            return $this->helper->isEnablePrefixField();
        } else {
            return $result;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Define if middle name attribute can be shown
     *
     * @param \Magento\Customer\Block\Widget\Name $subject
     * @param $result
     * @return int|mixed
     */
    public function afterShowMiddlename(\Magento\Customer\Block\Widget\Name $subject , $result)
    {
        if ($this->customerSession->isB2bAccount()) {
            return $this->helper->isEnableMiddleField();
        } else {
            return $result;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Define if suffix attribute can be shown
     *
     * @param \Magento\Customer\Block\Widget\Name $subject
     * @param $result
     * @return mixed|string
     */
    public function afterShowSuffix(\Magento\Customer\Block\Widget\Name $subject , $result)
    {
        if ($this->customerSession->isB2bAccount()) {
            return $this->helper->isEnableSuffixField();
        } else {
            return $result;
        }
    }
}
