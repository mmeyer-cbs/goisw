<?php
declare(strict_types=1);

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
namespace Bss\CompanyAccount\Ui\Component\Listing\SubUser;

use Bss\CompanyAccount\Model\ResourceModel\SubUser\Grid\CollectionFactory;
use Magento\Framework\Api\Filter;

/**
 * Class DataProvider
 *
 * @package Bss\CompanyAccount\Ui\Component\Listing\Role
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get roles data by company user
     *
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $data['items'] = [];
        if ($this->request->getParam('parent_id')) {
            $collection->addFieldToFilter(
                [
                    \Bss\CompanyAccount\Api\Data\SubUserInterface::CUSTOMER_ID,
                    \Bss\CompanyAccount\Api\Data\SubUserInterface::CUSTOMER_ID
                ],
                [
                    ["eq" => (int)$this->request->getParam('parent_id')],
                    ["null" => true]
                ]
            );

        }
        return $collection->toArray();
    }

    /**
     * Add full text search filter to collection
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter): void
    {
        if ($filter->getField() !== 'fulltext') {
            $this->collection->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        } else {
            $value = trim($filter->getValue());
            $collection = $this->getCollection();
            $collection->addFieldToFilter(
                [
                    'sub_id',
                    'customer_id',
                    'sub_name',
                    'sub_email',
                    'sub_status',
                    'role_name',
                    'created_at',
                    'quote_id',
                    'parent_quote_id',
                    'quote_status'
                ],
                [
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                ]
            );
            $this->collection = $collection;
        }
    }
}
