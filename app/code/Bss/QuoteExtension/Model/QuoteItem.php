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
namespace Bss\QuoteExtension\Model;

/**
 * Class QuoteItem
 */
class QuoteItem extends \Magento\Framework\Model\AbstractModel implements \Bss\QuoteExtension\Api\Data\QuoteItemInterface
{
    /**
     * { @inheritdoc }
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\QuoteExtension\Model\ResourceModel\QuoteItem::class);
    }

    /**
     * @inheritDoc
     */
    public function setItemId($itemId = null)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * @inheritDoc
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * @inheritDoc
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }
}
