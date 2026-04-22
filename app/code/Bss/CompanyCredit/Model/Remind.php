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
namespace Bss\CompanyCredit\Model;

use Bss\CompanyCredit\Api\Data\RemindInterface;
use Bss\CompanyCredit\Model\ResourceModel\Remind as ResourceModelRemind;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreFactory;

class Remind extends AbstractModel implements RemindInterface
{
    /**
     * Construct.
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct(
            $context,
            $registry
        );
    }

    /**
     * Construct.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModelRemind::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setIdHistory($idHistory)
    {
        return $this->setData(self::ID_HISTORY, $idHistory);
    }

    /**
     * {@inheritdoc}
     */
    public function setSendingTime($sendingTime)
    {
        return $this->setData(self::SENDING_TIME, $sendingTime);
    }

    /**
     * {@inheritdoc}
     */
    public function setSent($sent)
    {
        return $this->setData(self::SENT, $sent);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdHistory()
    {
        return $this->getData(self::ID_HISTORY);
    }

    /**
     * {@inheritdoc}
     */
    public function getSendingTime()
    {
        return $this->getData(self::SENDING_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function getSent()
    {
        return $this->getData(self::SENT);
    }

    /**
     * Load remind by id history
     *
     * @param int $idHistory
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByIdHistory($idHistory)
    {
        return $this->_getResource()->loadByIdHistory($this, $idHistory);
    }
}
