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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerAttributes data source
 */
class CustomerAttributes
{
    /**
     * @var CollectionFactory
     */
    protected $customerCollection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CustomerAttributes constructor.
     *
     * @param CollectionFactory $customerCollection
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $customerCollection,
        LoggerInterface $logger
    ) {
        $this->customerCollection = $customerCollection;
        $this->logger = $logger;
    }

    /**
     * Get customer groups
     *
     * @return array|\Magento\Customer\Api\Data\GroupInterface[]
     */
    public function getCustomerAttributes()
    {
        try {
            return $this->customerCollection->create()
                ->addFieldToFilter('attribute_code', ['neq' => 'group_id'])
                ->getItems();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return [];
        }
    }
}
