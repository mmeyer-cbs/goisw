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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Model\ResourceModel;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Bss\CompanyCredit\Api\Data\CreditInterface;
use Bss\CompanyCredit\Model\CreditFactory;
use Bss\CompanyCredit\Model\ResourceModel\Credit as CreditResource;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class CreditRepository implements CreditRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Credit
     */
    protected $creditResource;

    /**
     * @var \Bss\CompanyCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;

    /**
     * @var array
     */
    private $companyCreditRegistryByCustomer = [];

    /**
     * CreditRepository constructor
     *
     * @param LoggerInterface $logger
     * @param Credit $creditResource
     * @param CreditFactory $creditFactory
     * @param SessionFactory $customerSession
     */
    public function __construct(
        LoggerInterface $logger,
        CreditResource $creditResource,
        CreditFactory $creditFactory,
        SessionFactory $customerSession
    ) {
        $this->logger = $logger;
        $this->creditResource = $creditResource;
        $this->creditFactory = $creditFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Get companycredit by customer id
     *
     * @param int|null $customerId
     * @return CreditRepositoryInterface|\Bss\CompanyCredit\Model\Credit|mixed
     */
    public function get($customerId = null)
    {
        try {
            if ($customerId === null) {
                $customerId = (int)$this->customerSession->create()->getCustomer()->getId();
            }

            if (isset($this->companyCreditRegistryByCustomer[$customerId])) {
                return $this->companyCreditRegistryByCustomer[$customerId];
            }

            $creditModel = $this->creditFactory->create();
            return $creditModel->loadByCustomer($customerId);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return null;
        }
    }

    /**
     * Save credit
     *
     * @param CreditInterface $creditInterface
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(CreditInterface $creditInterface)
    {
        try {
            $this->creditResource->save($creditInterface);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            throw new CouldNotSaveException(
                __('Could not save company credit')
            );
        }
    }
}
