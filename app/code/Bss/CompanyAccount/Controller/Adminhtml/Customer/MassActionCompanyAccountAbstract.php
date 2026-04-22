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
namespace Bss\CompanyAccount\Controller\Adminhtml\Customer;

use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassActionCompanyAccountAbstract
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\Customer
 */
abstract class MassActionCompanyAccountAbstract extends \Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction
{
    const CA_ATTRIBUTE = 'bss_is_company_account';

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var int
     */
    protected $statusValue = null;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * MassApprovedCompanyAccount constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param Filter $filter
     * @param Data $helper
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        Data $helper,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->setCompanyStatusVal();
        parent::__construct($context, $filter, $collectionFactory);
    }

    /**
     * Set company status value for action
     *
     * @return int
     */
    abstract protected function setCompanyStatusVal();

    /**
     * Get company status val
     *
     * @return int
     */
    protected function getCompanyStatusVal()
    {
        return $this->statusValue;
    }

    /**
     * @inheritDoc
     */
    protected function massAction(AbstractCollection $collection)
    {
        $updatedCustomerCount = 0;
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getAllIds() as $cid) {
            $customer = $this->customerRepository->getById($cid);
            if ($this->helper->isEnable($customer->getWebsiteId())) {
                $currentCompanyAccountStatus = $customer->getCustomAttribute(self::CA_ATTRIBUTE);

                $hasChanged = false;
                if ($currentCompanyAccountStatus !== null) {
                    $isEqual = (int) $currentCompanyAccountStatus
                            ->getValue() === $this->getCompanyStatusVal();
                    if (!$isEqual) {
                        $hasChanged = true;
                    }
                } else {
                    $hasChanged = true;
                }

                try {
                    if ($hasChanged) {
                        $customer->setCustomAttribute(self::CA_ATTRIBUTE, $this->getCompanyStatusVal());
                        $this->customerRepository->save($customer);
                        $updatedCustomerCount++;
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('customer ID - %1: ' . $e->getMessage(), $cid));
                }
            }
        }
        $updatedCustomerCount ? $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $updatedCustomerCount)) :
            $this->messageManager->addSuccessMessage(__('No record were updated'));

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('customer/index/index');

        return $resultRedirect;
    }
}
