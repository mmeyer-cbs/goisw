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
interface QEOldInterface
{
    /**#@+
     * Constants defined for keys of data array
     */

    const ID = 'id';

    const QUOTE_IDS = 'quote_ids';

    const TYPE = 'type';

    /**
     * Set Quote IDs
     *
     * @param string|null $quoteIds
     * @return $this
     * @since 100.1.0
     */
    public function setQuoteIds($quoteIds = null);

    /**
     * Get Quote IDs
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getQuoteIds();

    /**
     * Set type
     *
     * @param string|null $type
     * @return $this
     * @since 100.1.0
     */
    public function setType($type);

    /**
     * Get Quote IDs
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getType();

}
