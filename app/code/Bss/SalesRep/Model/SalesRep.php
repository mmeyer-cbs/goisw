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
 * @category  BSS
 * @package   Bss_SalesRep
 * @author    Extension Team
 * @copyright Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Model
 */
class SalesRep extends \Magento\Framework\Model\AbstractModel implements \Bss\SalesRep\Api\Data\SalesRepInterface
{
    /**
     * Construct
     */
    public function _construct()
    {
        $this->_init(\Bss\SalesRep\Model\ResourceModel\SalesRep::class);
    }

    /**
     * @inheritDoc
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * @inheritDoc
     */
    public function setInformation($information)
    {
        return $this->setData(self::INFORMATION, $information);
    }

    /**
     * @inheritDoc
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getInformation()
    {
        return $this->getData(self::INFORMATION);
    }

    /**
     * @inheritDoc
     */
    public function getSalesRep()
    {
        return $this->getData();
    }
}
