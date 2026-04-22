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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Sales\Model\AdminOrder;

use \Bss\CustomerAttributes\Helper\File;

/**
 * Class ShippingInformationManagement
 */
class Create
{
    /**
     * @var File
     */
    public $helperFile;

    /**
     * Create constructor.
     * @param File $helperFile
     */
    public function __construct(
        File $helperFile
    ) {
        $this->helperFile = $helperFile;
    }

    /**
     * Save type address to download image and getName file
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Quote\Model\Quote\Address|array $addressData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetBillingAddress($subject, $addressData)
    {
        $this->helperFile->addressType = "billing_address";
        return [$addressData];
    }

    /**
     * Save type address to download image and getName file
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Quote\Model\Quote\Address|array $addressData
     * @return \Magento\Quote\Model\Quote\Address|array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetShippingAddress($subject, $addressData)
    {
        $this->helperFile->addressType = "shipping_address";
        return [$addressData];
    }

}
