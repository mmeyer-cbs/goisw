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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model\Category\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Class CustomBackend
 *
 * @package Bss\QuoteExtension\Model\Category\Attribute\Backend
 */
class CustomBackend extends AbstractBackend
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * CustomBackend constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Plugin Before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'quote_category_cus_group') {
            $this->updateBssAttribute($object, $attributeCode);
        }
        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        }
        return $this;
    }

    /**
     * Update form Data
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $attributeCode
     */
    private function updateBssAttribute($object, $attributeCode)
    {
        $data = $object->getData($attributeCode);
        if (!is_array($data)) {
            $data = [];
        }
        $object->setData($attributeCode, implode(',', $data) ?: null);
        if (empty($data)) {
            $object->setData($attributeCode, $this->getBssCustomerGroup($data));
        }
        if (count($data) == 1 && in_array(0, $data)) {
            $object->setData($attributeCode, implode(',', $data) ? '0' : '0');
        }
    }

    /**
     * Get customer group value
     *
     * @param array $data
     * @return string|null
     */
    private function getBssCustomerGroup($data)
    {
        $categoryPostData = $this->request->getPostValue();
        if (!empty($categoryPostData['use_config']['quote_category_cus_group'])) {
            return implode(',', $data) ?: null;
        } else {
            return implode(',', $data) ?: '';
        }
    }

    /**
     * After Load Attribute Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'quote_category_cus_group') {
            $data = $object->getData($attributeCode);
            if ($data) {
                if (!is_array($data)) {
                    $object->setData($attributeCode, explode(',', $data));
                } else {
                    $object->setData($attributeCode, $data);
                }
            }
        }
        return $this;
    }
}
