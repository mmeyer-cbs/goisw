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
namespace Bss\CompanyAccount\Model\ResourceModel;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Bss\CompanyAccount\Exception\EmailExistsException as AlreadyExistsException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class SubUser
 *
 * @package Bss\CompanyAccount\Model\ResourceModel
 */
class SubUser extends AbstractDb
{
    const TABLE = 'bss_sub_user';
    const ID = 'sub_id';

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * SubUser constructor.
     *
     * @param CustomerResource $customerResource
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param ResourceConnection $resource
     * @param null|string $connectionName
     */
    public function __construct(
        CustomerResource $customerResource,
        CustomerFactory $customerFactory,
        Context $context,
        ResourceConnection $resource,
        $connectionName = null
    ) {
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        $this->resource = $resource;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, self::ID);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinLeft(
            ['subuser_role' => $this->getTable(SubRole::TABLE_NAME)],
            $this->getTable(SubUser::TABLE) . '.role_id = subuser_role.role_id',
            ['role_name']
        );
        return $select;
    }

    /**
     * Validate unique email for sub-user before save to database
     *
     * @param \Magento\Framework\Model\AbstractModel $subUser
     * @return AbstractDb
     * @throws AlreadyExistsException
     * @throws ValidatorException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $subUser)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $customerId = $subUser->getCustomerId();

        if (!$subUser->getSubEmail()) {
            throw new ValidatorException(__('The sub-user email is missing. Enter and try again.'));
        }
        $this->validateUniqueSubEmail($customerId, $subUser->getSubEmail(), $subUser->getSubId());
        return parent::_beforeSave($subUser);
    }

    /**
     * Validate unique email sub-user
     *
     * @param int $customerId
     * @param string $email
     * @param int|null $subId
     *
     * @throws AlreadyExistsException
     */
    public function validateUniqueSubEmail($customerId, $email, $subId = null)
    {

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create();

        $this->customerResource->load($customer, $customerId);

        $subUserTableName = $this->resource->getTableName(self::TABLE);
        $customerTableName = $this->resource->getTableName('customer_entity');

        $connection = $this->getConnection();

        $subUserBind = ['sub_email' => $email];
        $customerBind = ['email' => $email];

        /* Fetch sub-user of specific website */
        $subUserSelect = $connection->select()->from(
            $subUserTableName,
            [self::ID])
            ->join(
            $customerTableName,
            $subUserTableName
            . '.customer_id = ' . $customerTableName . '.entity_id'
            )->where('sub_email = :sub_email');

        $customerSelect = $connection->select()->from(
            $customerTableName,
            ['entity_id'])
            ->where('email = :email');

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $websiteId = (int)$customer->getWebsiteId();

            $subUserBind['website_id'] = $websiteId;
            $subUserSelect->where('website_id = :website_id');

            $customerBind['website_id'] = $websiteId;
            $customerSelect->where('website_id = :website_id');
        }

        if ($subId) {
            $subUserBind[self::ID] = $subId;
            $subUserSelect->where(self::ID . ' != :' . self::ID);
        }
        $subUserResult = $connection->fetchOne($subUserSelect, $subUserBind);
        $customerResult = $connection->fetchOne($customerSelect, $customerBind);
        if ($subUserResult || $customerResult) {
            $ex = new AlreadyExistsException(
                __('A user with the same email address already exists in an associated website.')
            );
            $data = [
                'sub_id' => $subUserResult,
                'customer_id' => $customerResult
            ];
            throw $ex->setData($data);
        }
    }
}
