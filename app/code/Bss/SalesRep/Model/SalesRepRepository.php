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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model;

use Bss\SalesRep\Api\Data\SalesRepInterface;
use Bss\SalesRep\Api\SalesRepRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Model\UserFactory as UserCollectionFactory;

/**
 * Class SalesRepRepository
 *
 * @package Bss\SalesRep\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesRepRepository implements SalesRepRepositoryInterface
{
    /**
     * @var SalesRep
     */
    protected $salesRep;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var ResourceModel\SalesRep\CollectionFactory
     */
    protected $collection;

    /**
     * @var ResourceModel\SalesRep
     */
    protected $salesRepResource;

    /**
     * @var SalesRepFactory
     */
    protected $salesRepFactory;

    /**
     * @var SearchCriteriaInterface
     */
    protected $criteria;

    /**
     * @var \Magento\Authorization\Model\RoleFactory
     */
    protected $roleFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var UserCollectionFactory
     */
    protected $userFactory;

    /**
     * SalesRepRepository constructor.
     * @param SalesRepFactory $salesRepFactory
     * @param ResourceModel\SalesRep $salesRepResource
     * @param ResourceModel\SalesRep\CollectionFactory $collection
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessor $collectionProcessor
     * @param SalesRep $salesRep
     * @param SearchCriteriaInterface $criteria
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $collectionFactory
     * @param UserCollectionFactory $userFactory
     */
    public function __construct(
        \Bss\SalesRep\Model\SalesRepFactory $salesRepFactory,
        \Bss\SalesRep\Model\ResourceModel\SalesRep $salesRepResource,
        \Bss\SalesRep\Model\ResourceModel\SalesRep\CollectionFactory $collection,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessor $collectionProcessor,
        \Bss\SalesRep\Model\SalesRep $salesRep,
        SearchCriteriaInterface $criteria,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $collectionFactory,
        UserCollectionFactory $userFactory
    ) {
        $this->salesRepFactory = $salesRepFactory;
        $this->salesRepResource = $salesRepResource;
        $this->salesRep = $salesRep;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->collection = $collection;
        $this->criteria = $criteria;
        $this->roleFactory = $roleFactory;
        $this->jsonHelper = $jsonHelper;
        $this->collectionFactory = $collectionFactory;
        $this->userFactory = $userFactory;
    }

    /**
     * Get Sales Rep by id
     *
     * @param int $repId
     * @return SalesRep
     * @throws NoSuchEntityException
     */
    public function getById($repId)
    {
        try {
            return $this->salesRep->load($repId, 'rep_id');
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Sales Rep with id "%1" does not exist.', $repId));
        }
    }

    /**
     * Get Sales Rep by user id
     *
     * @param int $userId
     * @return SalesRep
     * @throws NoSuchEntityException
     */
    public function getByUserId($userId)
    {
        try {
            return $this->salesRep->load($userId, 'user_id');
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Sales Rep with user id "%1" does not exist.', $userId));
        }
    }

    /**
     * Get list Sales Rep
     *
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->collection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Delete Sales Rep
     *
     * @param SalesRepInterface $salesRep
     * @return mixed
     * @throws CouldNotDeleteException
     */
    public function delete(SalesRepInterface $salesRep)
    {
        try {
            return $this->salesRepResource->delete($salesRep);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
    }

    /**
     * Delete Sales Rep by id
     *
     * @param int $id
     * @return mixed
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        try {
            $salesRep = $this->salesRepFactory->create();
            $this->salesRepResource->load($salesRep, $id);

            return $this->delete($salesRep);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
    }

    /**
     * Get list user by role id
     *
     * @param int $roleId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getListByRoleId($roleId) {
        $response = [];
        try {
            $role = $this->roleFactory->create();
            $collection = $this->collectionFactory->create();
            $role->load($roleId);
            if ($role->getId()) {
                if ($role->getIsSalesRep() != null) {
                    $rolesSalesRep = $collection->addFieldToFilter('parent_id', $roleId);
                    $response['number_of_assigned_user'] = $rolesSalesRep->count();
                    $data = [];
                    foreach ($rolesSalesRep as $role) {
                        $userId = $role->getUserId();
                        $user = $this->userFactory->create()->load($userId);
                        $data[] = [
                            'user_id' => $user->getId(),
                            'user_name' => $user->getName()
                        ];
                    }
                    $response['users'] = $data;
                } else {
                    $response['error'] = _('Role is not sales rep.');
                }
            } else {
                $response['error'] = _('Can not get this role.');
            }
        } catch (\Exception $e) {
            throw new NoSuchEntityException(_('Can not get this role.'));
        }
        return $this->jsonHelper->jsonEncode($response);
    }
}
