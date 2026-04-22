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
namespace Bss\CompanyAccount\Model\SubRole;

use Bss\CompanyAccount\Model\ResourceModel\SubRole\CollectionFactory;
use Bss\CompanyAccount\Model\SubRole;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Data provider of customer addresses for customer address grid.
 *
 * @property \Bss\CompanyAccount\Model\ResourceModel\SubRole\Collection $collection
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param ContextInterface $context
     * @param array $meta
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        ContextInterface $context,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->context = $context;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get roles data
     *
     * @return array
     */
    public function getData(): array
    {
        if (null !== $this->loadedData) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var SubRole $item */
        foreach ($items as $item) {
            $roleId = $item->getRoleId();
            $this->loadedData[$roleId] = $item->getData();
        }
        $this->loadedData[''] = $this->getDefaultData();

        return $this->loadedData;
    }

    /**
     * Get default customer data for adding new role
     *
     * @return array
     */
    private function getDefaultData(): array
    {
        $customerId = $this->context->getRequestParam('customer_id');
        return [
            'customer_id' => $customerId
        ];
    }
}
