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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Paymentshipping\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Paymentshipping
 *
 * @package Bss\Paymentshipping\Model
 */
class Paymentshipping extends AbstractModel implements \Bss\Paymentshipping\Api\Data\PaymentshippingInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\Bss\Paymentshipping\Model\ResourceModel\Paymentshipping::class);
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId(int $websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritdoc
     */
    public function setMethod(string $method)
    {
        return $this->setData(self::METHOD, $method);
    }

    /**
     * @inheritdoc
     */
    public function setGroupIds(string $groupIds)
    {
        return $this->setData(self::GROUP_IDS, $groupIds);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return parent::getEntityId();
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * @inheritdoc
     */
    public function getGroupIds()
    {
        return $this->getData(self::GROUP_IDS);
    }

}
