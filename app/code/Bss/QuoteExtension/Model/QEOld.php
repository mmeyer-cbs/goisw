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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Magento\Framework\Model\AbstractModel;
use Bss\QuoteExtension\Api\Data\QEOldInterface;

/**
 * Class QEOld
 * Delete quote unnecessary
 */
class QEOld extends AbstractModel implements QEOldInterface
{
    const CACHE_TAG = 'quote_extension_old';

    protected $_cacheTag = 'quote_extension_old';

    protected $_eventPrefix = 'quote_extension_old';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\QEOld::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteIds($quoteIds = null)
    {
        return $this->setData(self::QUOTE_IDS, $quoteIds);
    }

    public function getQuoteIds()
    {
        return $this->getData(self::QUOTE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }
}
