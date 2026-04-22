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
 * @category BSS
 * @package Bss_CustomerAttributes
 * @author Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Controller\Index;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Checkout\Model\Session;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Viewfile
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Controller\Index
 */
class GetValue extends Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var Customerattribute
     */
    private $helper;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Session $checkoutSession
     * @param Json $json
     * @param Customerattribute $helper
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Session $checkoutSession,
        Json $json,
        Customerattribute $helper,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = '';
        $customerAttributes = $this->getRequest()->getParam('data');
        if ($customerAttributes) {
            $customerAttributes = $this->json->unserialize($customerAttributes);
            $resultJson = $this->getValue($customerAttributes);
        }

        $result = $this->resultJsonFactory->create();
        $result->setData(['convertValue' => $resultJson]);
        return $result;
    }

    /**
     * @param $customerAddressAttribute
     * @return false
     */
    private function getValue($customerAddressAttribute)
    {
        $value = '';
        if ($customerAddressAttribute['attribute_code']) {
            $attributeCode = trim($customerAddressAttribute['attribute_code'], '[]');
            try {
                if (!$this->helper->isCustomAddressAttribute($attributeCode)) {
                    return false;
                }
                $addressAttribute = $this->attributeRepository
                    ->get('customer_address', $attributeCode);
                $addressValue = $this->helper->getValueAddressAttributeForOrder(
                    $addressAttribute,
                    $customerAddressAttribute['value']
                );
                $value = [
                    'attribute_code' => $attributeCode,
                    'value' => $customerAddressAttribute['value'],
                    'label' => $addressValue
                ];
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->json->serialize($value);
    }
}
