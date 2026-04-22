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
namespace Bss\CompanyAccount\Helper;

use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class GeneratedToken
 *
 * @package Bss\CompanyAccount\Helper
 */
class GeneratedToken
{
    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * GetSubUserByToken constructor.
     *
     * @param DateTimeFactory $dateTimeFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\Math\Random $mathRandom,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->mathRandom = $mathRandom;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Generate reset password token for sub-user
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateResetPasswordTokenHash()
    {
        return $this->mathRandom->getRandomString(10);
    }

    /**
     * Generate reset password expires token for sub-user
     *
     * @return string
     */
    public function generateResetPasswordTokenExpires()
    {
        $expiresAt = $this->dateTimeFactory->create();
        $expiresAt->add(new \DateInterval('P3D'));
        return $expiresAt->format('Y-m-d H:i:s');
    }
}
