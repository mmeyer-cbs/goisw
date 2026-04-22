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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Api\Data;

/**
 * @api
 */
interface QuoteVersionInterface
{
    /**#@+
     * Constants defined for keys of data array
     */

    const ID = 'id';

    const QUOTE_ID = 'quote_id';

    const VERSION = 'version';

    const STATUS = "status";

    const CREATED_AT = "created_at";

    const COMMENT = "comment";

    const AREA_LOG = "area_log";

    const LOG = "log";

    const QUOTE_ID_NOT_COMMENT = "quote_id_not_comment";

    /**
     * Set Quote ID
     *
     * @param int|null $quoteId
     * @return $this
     * @since 100.1.0
     */
    public function setQuoteId($quoteId = null);

    /**
     * Get Quote ID
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getQuoteId();

    /**
     * Set Version
     *
     * @param int|null $version
     * @return $this
     * @since 100.1.0
     */
    public function setVersion($version);

    /**
     * Get Version
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getVersion();

    /**
     * Set Status
     *
     * @param string|null $status
     * @return $this
     * @since 100.1.0
     */
    public function setStatus($status);

    /**
     * Get Status
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getStatus();


    /**
     * Get Created At
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getCreatedAt();

    /**
     * Set Area log
     *
     * @param string|null $areaLog
     * @return $this
     * @since 100.1.0
     */
    public function setAreaLog($areaLog);

    /**
     * Get Area log
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getAreaLog();

    /**
     * Set Log
     *
     * @param string|null $log
     * @return $this
     * @since 100.1.0
     */
    public function setLog($log);

    /**
     * Get Log
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getLog();

    /**
     * Set Quote Id Not comment
     *
     * @param int|null $quoteIdNotComment
     * @return $this
     * @since 100.1.0
     */
    public function setQuoteIdNotComment($quoteIdNotComment);

    /**
     * Get Quote Id Not comment
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getQuoteIdNotComment();

}
