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
namespace Bss\CompanyCredit\Api\Data;

/**
 * @api
 */
interface RemindInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID_HISTORY = 'id_history';

    const SENDING_TIME = 'sending_time';

    const SENT = 'sent';
    /**#@-*/

    /**
     * Set history id
     *
     * @param int $idHistory
     * @return $this
     * @since 100.1.0
     */
    public function setIdHistory($idHistory);

    /**
     * Set Sending Time
     *
     * @param string|null $sendingTime
     * @return $this
     * @since 100.1.0
     */
    public function setSendingTime($sendingTime);

    /**
     * Set Sent
     *
     * @param int|null $sent
     * @return $this
     * @since 100.1.0
     */
    public function setSent($sent);

    /**
     * Get history id
     *
     * @return string
     * @since 100.1.0
     */
    public function getIdHistory();

    /**
     * Get Sending Time
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getSendingTime();

    /**
     * Get Sent
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getSent();
}
