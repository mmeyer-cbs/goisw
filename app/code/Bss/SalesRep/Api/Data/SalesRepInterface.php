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
namespace Bss\SalesRep\Api\Data;

/**
 * @api
 */
interface SalesRepInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const USER_ID = 'user_id';

    const INFORMATION = 'information';

    /**#@-*/

    /**
     * Get Sales Rep
     *
     * @return mixed
     */
    public function getSalesRep();

    /**
     * Set User Id
     *
     * @param int $id
     * @return mixed
     */
    public function setUserId($id);

    /**
     *  Set Information
     *
     * @param string $information
     * @return $this
     * @since 100.1.0
     */
    public function setInformation($information);

    /**
     * Get User Id
     *
     * @return int
     * @since 100.1.0
     */
    public function getUserId();

    /**
     * Get Information
     *
     * @return string
     * @since 100.1.0
     */
    public function getInformation();
}
