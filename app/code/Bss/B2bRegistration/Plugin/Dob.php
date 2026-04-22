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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Plugin;

use Bss\B2bRegistration\Helper\Data;
use Magento\Framework\View\Element\Html\Date;
use Magento\Customer\Api\CustomerMetadataInterface;

class Dob
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Date
     */
    protected $dateElement;

    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Dob constructor.
     * @param Data $helper
     * @param Date $dateElement
     * @param CustomerMetadataInterface $customerMetadata
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        Data $helper,
        Date $dateElement,
        CustomerMetadataInterface $customerMetadata,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->helper = $helper;
        $this->dateElement = $dateElement;
        $this->customerMetadata = $customerMetadata;
        $this->timezone = $timezone;
    }

    /**
     * Get Date of birth Html Field
     *
     * @param \Magento\Customer\Block\Widget\Dob $subject
     * @param \Closure $proceed
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetFieldHtml(\Magento\Customer\Block\Widget\Dob $subject, \Closure $proceed)
    {
        if ($this->helper->isEnable()) {
            $this->dateElement->setData([
                'extra_params' => $this->getHtmlExtraParams($subject->getDateFormat()),
                'name' => $subject->getHtmlId(),
                'id' => $subject->getHtmlId(),
                'class' => $subject->getHtmlClass(),
                'value' => $subject->getValue(),
                'date_format' => $subject->getDateFormat(),
                'image' => $subject->getViewFileUrl('Magento_Theme::calendar.png'),
                'years_range' => '-120y:c+nn',
                'max_date' => '-1d',
                'change_month' => 'true',
                'change_year' => 'true',
                'show_on' => 'both'
            ]);
            return $this->dateElement->getHtml();
        } else {
            return $proceed();
        }
    }

    /**
     * Get Extra Params
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHtmlExtraParams($date)
    {
        $extraParams = [
            "'validate-date':{'dateFormat': '$date'}"
        ];

        if ($this->isRequired()) {
            $extraParams[] = 'required:true';
        }

        $extraParams = implode(', ', $extraParams);

        return 'data-validate="{' . $extraParams . '}"';
    }

    /**
     * Check Required
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isRequired()
    {
        $attributeMetadata = $this->getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isRequired() : false;
    }

    /**
     * Retrieve customer attribute instance
     *
     * @param string $attributeCode
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttribute($attributeCode)
    {
        try {
            return $this->customerMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
