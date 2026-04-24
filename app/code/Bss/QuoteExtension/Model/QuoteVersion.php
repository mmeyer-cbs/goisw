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

use Bss\QuoteExtension\Api\Data\QuoteVersionInterface;

class QuoteVersion extends \Magento\Framework\Model\AbstractModel implements \Bss\QuoteExtension\Api\Data\QuoteVersionInterface
{
    /**
     * { @inheritdoc }
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\QuoteExtension\Model\ResourceModel\QuoteVersion::class);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId($quoteId = null)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setVersion($version)
    {
        return $this->setData(self::VERSION, $version);
    }

    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        return $this->getData(self::VERSION);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setAreaLog($areaLog)
    {
        return $this->setData(self::AREA_LOG, $areaLog);
    }

    /**
     * @inheritDoc
     */
    public function getAreaLog()
    {
        return $this->getData(self::AREA_LOG);
    }

    /**
     * @inheritDoc
     */
    public function setLog($log)
    {
        return $this->setData(self::LOG, $log);
    }

    /**
     * @inheritDoc
     */
    public function getLog()
    {
        return $this->getData(self::LOG);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteIdNotComment($quoteIdNotComment)
    {
        return $this->setData(self::QUOTE_ID_NOT_COMMENT, $quoteIdNotComment);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteIdNotComment()
    {
        return $this->getData(self::QUOTE_ID_NOT_COMMENT);
    }
}
