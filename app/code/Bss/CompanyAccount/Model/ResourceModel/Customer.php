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

use Magento\Customer\Model\Config\Share;

/**
 * Class customer
 *
 * @package Bss\CompanyAccount\Model\ResourceModel
 */
class Customer
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * customer constructor.
     *
     * @param Share $shareConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Share $shareConfig,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->shareConfig = $shareConfig;
    }

    /**
     * Validate unique customer
     *
     * @param string $customerEmail
     * @param int $websiteId
     * @return string
     */
    public function validateUniqueCustomer($customerEmail, $websiteId)
    {
        /** Begin validate unique email compatible with our module */
        $connection = $this->resource->getConnection();

        $customerBind = ['sub_email' => $customerEmail];
        $subUserTableName = $this->resource->getTableName('bss_sub_user');
        $customerTableName = $this->resource->getTableName('customer_entity');

        /* Fetch sub-user of specific website */
        $subUserSelect = $connection->select()->from(
            $subUserTableName,
            ['sub_id'])
            ->join(
            $customerTableName,
            $subUserTableName
            . '.customer_id = ' . $customerTableName . '.entity_id'
            )->where('sub_email = :sub_email');

        if ($this->shareConfig->isWebsiteScope()) {
            $subUserSelect->where('website_id = :website_id');
            $customerBind['website_id'] = $websiteId;
        }

        return $connection->fetchOne($subUserSelect, $customerBind);
    }
}
