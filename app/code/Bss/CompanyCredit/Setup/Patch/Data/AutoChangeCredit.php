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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\CompanyCredit\Setup\Patch\Data;

/**
 * Data patch format
 */
class AutoChangeCredit implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var \Bss\CompanyCredit\Model\UpdatePaymentStatus
     */
    protected $paymentStatus;

    /**
     * Construct.
     *
     * @param \Bss\CompanyCredit\Model\UpdatePaymentStatus $paymentStatus
     */
    public function __construct(
        \Bss\CompanyCredit\Model\UpdatePaymentStatus $paymentStatus
    ) {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * Update payment credit.
     *
     * @return void
     */
    public function apply()
    {
        $this->paymentStatus->executeFirstUpgradeData();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
