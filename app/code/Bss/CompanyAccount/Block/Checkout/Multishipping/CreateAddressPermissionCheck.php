<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\Checkout\Multishipping;

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;

/**
 * Class CreateAddressPermissionCheck
 *
 * @package Bss\CompanyAccount\Block\Checkout\Multishipping
 */
class CreateAddressPermissionCheck extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PermissionsChecker
     */
    private $checker;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * CreateAddressPermissionCheck constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param PermissionsChecker $checker
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        PermissionsChecker $checker,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->checker = $checker;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * If sub-user cant view and add address book
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cantCreateAddress()
    {
        if ($this->helper->isEnable()) {
            return $this->checker->isDenied(Permissions::ADD_VIEW_ADDRESS_BOOK);
        }
        return false;
    }

    /**
     * Get serializer object
     *
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getSerializer()
    {
        return $this->serializer;
    }
}
