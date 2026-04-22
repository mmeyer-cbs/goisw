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

use Bss\CompanyAccount\Api\Data\SubRoleInterface;
use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Exception\RelationMethodNotFoundException;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Model\ResourceModel\SubUser as ResourceModel;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class SubUser
 *
 * @package Bss\CompanyAccount\Model
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class SubUser extends AbstractModel implements SubUserInterface
{
    /**
     * @var SubRoleRepositoryInterface
     */
    private $subRoleRepository;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * SubUser constructor.
     *
     * @param SubRoleRepositoryInterface $subRoleRepository
     * @param CustomerResource $customerResource
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        SubRoleRepositoryInterface $subRoleRepository,
        CustomerResource $customerResource,
        CustomerFactory $customerFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->subRoleRepository = $subRoleRepository;
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init SubUser model
     *
     * @return void
     */
    // @codingStandardsIgnoreLine
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Retrieve label of sub-user status
     *
     * @return \Magento\Framework\Phrase
     */
    public function getStatusLabel()
    {
        return $this->getSubStatus() === \Bss\CompanyAccount\Model\Config\Source\EnableDisable::ENABLE ?
            __('Enable') : __('Disable');
    }

    /**
     * Sub-user can access
     *
     * @param int $value
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterface $role
     * @return bool
     */
    public function canAccess($value, $role): bool
    {
        if ($role->getRoleType() === null || trim($role->getRoleType()) === "") {
            return false;
        }

        $roleTypes = explode(',', $role->getRoleType());
        foreach ($roleTypes as $type) {
            if ((int) $type === Permissions::ADMIN || (int) $value === (int) $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get quote id
     *
     * @return int|mixed
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Set quote id
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setQuoteId($id)
    {
        return $this->setData(self::QUOTE_ID, $id);
    }

    /**
     * Get parent quote id
     *
     * @return int|mixed
     */
    public function getParentQuoteId()
    {
        return $this->getData(self::PARENT_QUOTE_ID);
    }

    /**
     * Set parent quote id
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setParentQuoteId($id)
    {
        return $this->setData(self::PARENT_QUOTE_ID, $id);
    }

    /**
     * Get create time
     *
     * @return mixed|string
     */
    public function getCreateTime()
    {
        return $this->getData(self::CREATE_AT);
    }

    /**
     * Set create time
     *
     * @param string $time
     * @return SubUser|void
     */
    public function setCreateTime($time)
    {
        return $this->setData(self::CREATE_AT, $time);
    }

    /**
     * Get identifier
     *
     * @return int|mixed
     */
    public function getSubId()
    {
        return (int)$this->getData(self::ID);
    }

    /**
     * Set sub user id
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setSubId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get associate company customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return (int)$this->getData(self::CUSTOMER_ID);
    }

    /**
     * Associate to a company account
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setCustomerId($id)
    {
        return $this->setData(self::CUSTOMER_ID, $id);
    }

    /**
     * Get sub user name
     *
     * @return mixed|string
     */
    public function getSubName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set sub user's name
     *
     * @param string $name
     * @return mixed|void
     */
    public function setSubName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get sub user's email
     *
     * @return mixed|string
     */
    public function getSubEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set email for sub user
     *
     * @param string $email
     * @return SubUser|void
     */
    public function setSubEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get sub user's password
     *
     * @return mixed|string
     */
    public function getSubPassword()
    {
        return $this->getData(self::PASSWORD);
    }

    /**
     * Set password for sub user
     *
     * @param string $password
     * @return SubUser|void
     */
    public function setSubPassword($password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    /**
     * Get sub user status
     *
     * @return int|mixed
     */
    public function getSubStatus()
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * Set sub user status
     *
     * @param int $status
     * @return SubUser|void
     */
    public function setSubStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get token
     *
     * @return mixed|string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * Set token
     *
     * @param string $token
     * @return SubUser|void
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Get associated role
     *
     * @return int|mixed
     */
    public function getRoleId()
    {
        return $this->getData(self::ROLE_ID);
    }

    /**
     * Associate sub user to role
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setRoleId($id)
    {
        return $this->setData(self::ROLE_ID, $id);
    }

    /**
     * Get token expires time
     *
     * @return string
     */
    public function getTokenExpiresAt()
    {
        return $this->getData(self::TOKEN_EXPIRES_AT);
    }

    /**
     * Set expires time
     *
     * @param string $date
     * @return SubUser|void
     */
    public function setTokenExpiresAt($date)
    {
        return $this->setData(self::TOKEN_EXPIRES_AT, $date);
    }

    /**
     * Get is sent mail value
     *
     * @return bool
     */
    public function getIsSentMail()
    {
        return (bool) (int) $this->getData(self::IS_SENT_MAIL);
    }

    /**
     * Set is sent mail value
     *
     * @param int $value
     * @return SubUser|void
     */
    public function setIsSentMail($value)
    {
        return $this->setData(self::IS_SENT_MAIL, $value);
    }

    /**
     * @inheritDoc
     */
    public function role(): ?SubRoleInterface
    {
        try {
            if (!$this->hasData(self::ROLE) &&
                ($this->getRoleId() || $this->getRoleId() === "0")
            ) {
                $role = $this->subRoleRepository->getById($this->getRoleId());

                if ($role->getRoleId() || $role->getRoleId() === "0") {
                    $this->setData(self::ROLE, $role);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical(
                __("BSS.ERROR: Get role from sub-user. %1", $e)
            );
        }

        return $this->getData(self::ROLE);
    }

    /**
     * @inheritDoc
     */
    public function customer(): ?Customer
    {
        try {
            if (!$this->hasData(self::CUSTOMER)) {
                $customer = $this->customerFactory->create();
                $this->customerResource->load($customer, $this->getCustomerId());
                if ($customer->getId()) {
                    $this->setData(self::CUSTOMER, $customer);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical(
                __("BSS.ERROR: Get customer from sub-user. %1", $e)
            );
        }

        return $this->getData(self::CUSTOMER);
    }

    /**
     * Eager loading relations data
     *
     * @param mixed $relationFields
     * @throws RelationMethodNotFoundException
     * @return SubUserInterface
     */
    public function with($relationFields): SubUserInterface
    {
        if (!is_array($relationFields)) {
            $relationFields = [$relationFields];
        }

        foreach ($relationFields as $field) {
            if (!method_exists($this, $field)) {
                throw new RelationMethodNotFoundException(
                    __("The relation %1 was not defined in model", $field)
                );
            }

            $this->$field();
        }

        return $this;
    }
}
