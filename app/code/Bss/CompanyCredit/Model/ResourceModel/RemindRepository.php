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
namespace Bss\CompanyCredit\Model\ResourceModel;

use Bss\CompanyCredit\Api\Data\RemindInterface;
use Bss\CompanyCredit\Api\RemindRepositoryInterface;
use Bss\CompanyCredit\Model\RemindFactory;
use Bss\CompanyCredit\Model\ResourceModel\Remind as RemindResource;
use Psr\Log\LoggerInterface;

class RemindRepository implements RemindRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Remind
     */
    protected $remindResource;

    /**
     * @var \Bss\CompanyCredit\Model\RemindFactory
     */
    private $remindFactory;

    /**
     * @var array
     */
    private $remindByIdHistory = [];

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param Remind $remindResource
     * @param RemindFactory $remindFactory
     */
    public function __construct(
        LoggerInterface $logger,
        RemindResource $remindResource,
        RemindFactory $remindFactory
    ) {
        $this->logger = $logger;
        $this->remindResource = $remindResource;
        $this->remindFactory = $remindFactory;
    }

    /**
     * Get remind by id
     *
     * @param int $remindIdHistory
     * @return RemindRepositoryInterface|\Bss\CompanyCredit\Model\Remind|mixed|null
     */
    public function getByIdHistory($remindIdHistory)
    {
        try {
            if (isset($this->remindByIdHistory[$remindIdHistory])) {
                return $this->remindByIdHistory[$remindIdHistory];
            }

            $remindModel = $this->remindFactory->create();
            return $remindModel->loadByIdHistory($remindIdHistory);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return null;
        }
    }

    /**
     * Save remind
     *
     * @param remindInterface $remindInterface
     * @return void
     */
    public function save($remindInterface)
    {
        try {
            $this->remindResource->save($remindInterface);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * Save remind
     *
     * @param array $data
     * @return void
     */
    public function insertMultiple($data)
    {
        try {
            $this->remindResource->insertMultiple($data);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * Delete remind
     *
     * @param remindInterface $remindInterface
     * @return void
     */
    public function delete($remindInterface)
    {
        try {
            $this->remindResource->delete($remindInterface);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
