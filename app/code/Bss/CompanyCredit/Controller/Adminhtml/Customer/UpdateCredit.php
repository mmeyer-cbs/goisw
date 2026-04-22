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
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Controller\Adminhtml\Customer;

use Bss\CompanyCredit\Helper\Model as HelperModel;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class UpdateCredit extends Action implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CustomerCollection
     */
    protected $customerCollection;
    /**
     * @var HelperModel
     */
    protected $helperModel;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * UpdateCredit constructor.
     *
     * @param Filter $filter
     * @param CustomerCollection $customerCollection
     * @param HelperModel $helperModel
     * @param CustomerRepository $customerRepository
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        CustomerCollection $customerCollection,
        HelperModel $helperModel,
        CustomerRepository $customerRepository,
        Context $context
    ) {
        $this->filter = $filter;
        $this->customerCollection = $customerCollection;
        $this->helperModel = $helperModel;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $result */
        $customerCollection = $this->getCustomerCollection();
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setRefererUrl();
        $params = $this->getRequest()->getParams();
        if (count($customerCollection)) {
            $dataMessage = [];
            $dataMessage["error"] = [];
            foreach ($customerCollection as $customer) {
                try {
                    $this->helperModel->saveCompanyCredit($customer, $params, "massUpdateCredit", $dataMessage);
                } catch (Exception $exception) {
                    $this->helperModel->logError($exception->getMessage());
                }
            }
            if ($dataMessage["error"]) {
                $this->messageManager->addErrorMessage(__(
                    "Customer ID %1 cannot have available credit greater than credit limit.",
                    implode(",", $dataMessage["error"])
                ));
            }
            if (isset($dataMessage["success"])) {
                $this->messageManager->addSuccessMessage(__("You have successfully saved changes to company credit"));
            }
        }
        return $result;
    }

    /**
     * Get customer by requested mass
     *
     * @return DataObject[]
     * @throws LocalizedException
     */
    private function getCustomerCollection()
    {
        $collection = $this->customerCollection->create();
        $collection = $this->filter->getCollection($collection);

        return $collection->getItems();
    }

    /**
     * Check acl
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CompanyCredit::saveCompanyCredit');
    }
}
