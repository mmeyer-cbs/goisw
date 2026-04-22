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
use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubRole as RoleResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Bss\CompanyAccount\Api\Data\SubRoleSearchResultsInterfaceFactory as SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class SubRoleRepository
 *
 * @package Bss\CompanyAccount\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubRoleRepository implements SubRoleRepositoryInterface
{
    /**
     * @var RoleResource
     */
    private $roleResource;

    /**
     * @var SubRoleFactory
     */
    private $roleFactory;

    /**
     * @var RoleResource\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SubRoleRepository constructor.
     *
     * @param RoleResource $roleResource
     * @param SubRoleFactory $roleFactory
     * @param CollectionProcessor $collectionProcessor
     * @param RoleResource\CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        RoleResource $roleResource,
        SubRoleFactory $roleFactory,
        CollectionProcessor $collectionProcessor,
        ResourceModel\SubRole\CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->roleResource = $roleResource;
        $this->roleFactory = $roleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Get role by id
     *
     * @param int $id
     * @return SubRoleInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        try {
            $role = $this->roleFactory->create();
            $this->roleResource->load($role, $id);

            return $role;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NoSuchEntityException(__('Can not get this role.'));
        }
    }

    /**
     * Save a role
     *
     * @param SubRoleInterface $role
     * @return SubRoleInterface|RoleResource
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(SubRoleInterface $role)
    {
        return $this->roleResource->save($role);
    }

    /**
     * Retrieve roles matching the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return \Bss\CompanyAccount\Api\Data\SubRoleSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        /** @var \Bss\CompanyAccount\Model\ResourceModel\SubUser\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete role
     *
     * @param SubRoleInterface $role
     * @return bool|RoleResource
     * @throws CouldNotDeleteException
     */
    public function delete(SubRoleInterface $role)
    {
        try {
            return $this->roleResource->delete($role);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
    }

    /**
     * Delete role by id
     *
     * @param int $id
     *
     * @return bool|RoleResource
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        try {
            $role = $this->roleFactory->create();
            $this->roleResource->load($role, $id);

            return $this->delete($role);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getListByCustomer($customerId): SearchResultsInterface
    {
        /* Use filter group for OR condition */
        $filterCustomer = $this->filterBuilder
            ->setField('customer_id')
            ->setConditionType('eq')
            ->setValue($customerId)
            ->create();
        $filterAdminRole = $this->filterBuilder
            ->setField('customer_id')
            ->setConditionType('null')
            ->setValue(null)
            ->create();
        $filterGroup = $this->filterGroupBuilder
            ->addFilter($filterCustomer)
            ->addFilter($filterAdminRole)
            ->create();
        $this->criteriaBuilder->setFilterGroups([$filterGroup]);

        return $this->getList(
            $this->criteriaBuilder->create()
        );
    }

    /**
     * Get Role to send email
     *
     * @param SubRoleInterface|string|array $newSubRole
     * @return array|string|SubRoleInterface
     * @throws NoSuchEntityException
     */
    public function getRoleToSendMail($newSubRole)
    {
        if (gettype($newSubRole) === 'array' && count($newSubRole) < 7) {
            $newRole = $this->getById($newSubRole['id']);
            $newSubRole['role_name'] = $newRole->getRoleName();
            $newSubRole['role_type'] = $newRole->getRoleType();
        }
        if (gettype($newSubRole) === 'string') {
            $newRole = $this->getById((int) $newSubRole);
            $newSubRole = [];
            $newSubRole['role_name'] = $newRole->getRoleName();
            $newSubRole['role_type'] = $newRole->getRoleType();
        }
        if (gettype($newSubRole['role_type'])==='string') {
            $newSubRole['role_type'] = explode(',', $newSubRole['role_type']);
        }
        for ($i = 0; $i < count($newSubRole['role_type']); $i++) {
            if ((int) $newSubRole['role_type'][$i] <= 0) {
                unset($newSubRole['role_type'][$i]);
            }
        }
        return $newSubRole;
    }
}
