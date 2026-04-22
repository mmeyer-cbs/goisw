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
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Api;

/**
 * Save company credit by api
 *
 * @api
 * @since 100.0.0
 */
interface SaveInterface
{
    /**
     * Save company credit by api
     *
     * @param string[] $saveCompanyCredit
     * @return mixed
     */
    public function save(
        $saveCompanyCredit
    );

    /**
     * Save company credit : direct udpate available credit
     *
     * @param string[] $saveCompanyCredit
     * @return mixed
     */
    public function saveDirectAvaliableCredit(
        $saveCompanyCredit
    );
}
