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

use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUserQuote as ResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class SubUserOrder
 *
 * @package Bss\CompanyAccount\Model
 */
class SubUserQuote extends AbstractModel implements SubUserQuoteInterface
{
    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * Initialize resource model
     *
     * @param Context $context
     * @param Registry $registry
     * @param SubUserRepositoryInterface $subUserRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        SubUserRepositoryInterface $subUserRepository,
        AbstractDb                 $resourceCollection = null,
        AbstractResource           $resource = null,
        array                      $data = []
    ) {
        $this->subUserRepository = $subUserRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @return array|int|mixed|null
     */
    public function getSubId()
    {
        return $this->getData(self::SUB_USER_ID);
    }

    /**
     * Set sub-user id
     *
     * @param int $id
     * @return SubUserQuoteInterface|void
     */
    public function setSubId($id)
    {
        return $this->setData(self::SUB_USER_ID, $id);
    }

    /**
     * Set sub-user id
     *
     * @param int $id
     * @return SubUserQuote|void
     */
    public function setQuoteId($id)
    {
        return $this->setData(self::QUOTE_ID, $id);
    }

    /**
     * Get quote id
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Get sub user quote status
     *
     * @return array|mixed|null
     */
    public function getActionBy()
    {
        return $this->getData(self::ACTION_BY);
    }

    /**
     * Set action by
     *
     * @param int $id
     * @return SubUserQuote|null
     */
    public function setActionBy($id): ?SubUserQuote
    {
        return $this->setData(self::ACTION_BY, $id);
    }

    /**
     * Get sub user quote status
     *
     * @return array|mixed|null
     */
    public function getQuoteStatus()
    {
        return $this->getData(self::QUOTE_STATUS);
    }

    /**
     * Set sub user quote status
     *
     * @param string $status
     * @return SubUserQuote|mixed
     */
    public function setQuoteStatus($status)
    {
        return $this->setData(self::QUOTE_STATUS, $status);
    }

    /**
     * Get sub user quote status
     *
     * @return array|mixed|null
     */
    public function getIsBackQuote()
    {
        return $this->getData(self::IS_BACK_QUOTE);
    }

    /**
     * Set sub user quote status
     *
     * @param bool $check
     * @return SubUserQuote|mixed
     */
    public function setIsBackQuote($check)
    {
        return $this->setData(self::IS_BACK_QUOTE, $check);
    }
}
