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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Observer\Adminhtml\Order;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;

/**
 * Visitor Observer
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AddressSave implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var OrderAddressInterface
     */
    private $orderAddressInterface;
    /**
     * @var OrderAddressRepositoryInterface
     */
    private $orderAddressRepository;
    /**
     * @var Customerattribute
     */
    private $bssHelper;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * AfterLoadOrder constructor.
     * @param RequestInterface $request
     * @param OrderAddressInterface $orderAddressInterface
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param Customerattribute $bssHelper
     * @param Json $json
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        RequestInterface $request,
        OrderAddressInterface $orderAddressInterface,
        OrderAddressRepositoryInterface $orderAddressRepository,
        Customerattribute $bssHelper,
        Json $json,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->request = $request;
        $this->orderAddressInterface = $orderAddressInterface;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->bssHelper = $bssHelper;
        $this->json = $json;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $addressId =$this->request->getParam('address_id');
        $address = $this->orderAddressInterface->load($addressId);
        $data = $this->request->getPostValue();
        $data['customer_address_attribute'] = $this->getCustomerAddressAttribute($data);
        $address->addData($data);
        $this->orderAddressRepository->save($address);
    }

    /**
     * Get json customer address attribute
     *
     * @param array $data
     * @return bool|AddressSave|string|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerAddressAttribute($data)
    {
        $customerAddressAttribute = [];
        foreach ($data as $code => $attributeValue) {
            if ($this->bssHelper->isCustomAddressAttribute($code)) {
                $attribute = $this->attributeRepository->get('customer_address',$code);
                $value = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $this->bssHelper->getValueAddressAttributeForOrder($attribute, $attributeValue),
                ];
                if ($attribute->getFrontendInput() == "file") {
                    $value['valueOld'] = $attributeValue["value"];
                }
                $customerAddressAttribute[$code] = $value;
            }
        }
        return $this->json->serialize($customerAddressAttribute);
    }
}
