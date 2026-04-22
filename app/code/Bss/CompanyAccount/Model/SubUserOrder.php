<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserOrderInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUserOrder as ResourceModel;

/**
 * Class SubUserOrder
 *
 * @package Bss\CompanyAccount\Model
 */
class SubUserOrder extends \Magento\Framework\Model\AbstractModel implements SubUserOrderInterface
{
    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * SubUserOrder constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param SubUserRepositoryInterface $subUserRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        SubUserRepositoryInterface $subUserRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->subUserRepository = $subUserRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init SubUser model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get subuser id
     *
     * @return int
     */
    public function getSubId()
    {
        return $this->getData(self::SUB_USER_ID);
    }

    /**
     * Set sub-user id
     *
     * @param int $id
     * @return SubUserOrder|void
     */
    public function setSubId($id)
    {
        return $this->setData(self::SUB_USER_ID, $id);
    }

    /**
     * Get order id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order
     *
     * @param int $id
     * @return SubUserOrder|void
     */
    public function setOrderId($id)
    {
        return $this->setData(self::ORDER_ID, $id);
    }

    /**
     * Get grand total
     *
     * @return string
     */
    public function getGrandTotal()
    {
        return $this->getData(self::GRAND_TOTAL);
    }

    /**
     * Set grand total
     *
     * @param string $total
     * @return SubUserOrder|void
     */
    public function setGrandTotal($total)
    {
        return $this->setData(self::GRAND_TOTAL, $total);
    }

    /**
     * Get sub-user info
     *
     * @return array
     */
    public function getSubUserInfo()
    {
        if ($this->getData(self::SUB_USER_INFO)) {
            return $this->serializer->unserialize($this->getData(self::SUB_USER_INFO));
        }
        return [];
    }

    /**
     * Set sub-user info
     *
     * @param string $data
     * @return SubUserOrder|void
     */
    public function setSubUserInfo($data)
    {
        return $this->setData(self::SUB_USER_INFO, $data);
    }

    /**
     * @inheritDoc
     */
    public function subUser(): ?SubUserInterface
    {
        try {
            if (!$this->hasData(self::SUB_USER)) {
                $subUser = $this->subUserRepository->getById($this->getSubId());
                if ($subUser->getId()) {
                    $this->setData(self::SUB_USER, $subUser);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical(
                __("BSS.ERROR: failed to get sub-user. %1", $e)
            );
        }

        return $this->getData(self::SUB_USER);
    }
}
