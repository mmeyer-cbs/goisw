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
interface QuoteItemInterface
{
    /**#@+
     * Constants defined for keys of data array
     */

    const ID = 'id';

    const ITEM_ID = 'item_id';

    const COMMENT = 'comment';

    /**
     * Set Item id
     *
     * @param int $itemId
     * @return $this
     * @since 100.1.0
     */
    public function setItemId($itemId = null);

    /**
     * Get Item Id
     *
     * @return int
     * @since 100.1.0
     */
    public function getItemId();

    /**
     * Set Comment
     *
     * @param string|null $comment
     * @return $this
     * @since 100.1.0
     */
    public function setComment($comment);

    /**
     * Get Comment
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getComment();

}
