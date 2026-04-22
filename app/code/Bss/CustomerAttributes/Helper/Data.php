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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Helper;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;

/**
 * Class Data
 *
 * @package Bss\CustomerAttributes\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_SORT_ORDER = 140;

    private static $tagMeta = [
        'script' => ['id' => 'script-src', 'remote' => ['src'], 'hash' => true],
        'style' => ['id' => 'style-src', 'remote' => [], 'hash' => true],
        'img' => ['id' => 'img-src', 'remote' => ['src']],
        'audio' => ['id' => 'media-src', 'remote' => ['src']],
        'video' => ['id' => 'media-src', 'remote' => ['src']],
        'track' => ['id' => 'media-src', 'remote' => ['src']],
        'source' => ['id' => 'media-src', 'remote' => ['src']],
        'object' => ['id' => 'object-src', 'remote' => ['data', 'archive']],
        'embed' => ['id' => 'object-src', 'remote' => ['src']],
        'applet' => ['id' => 'object-src', 'remote' => ['code', 'archive']],
        'link' => ['id' => 'style-src', 'remote' => ['href']],
        'form' => ['id' => 'form-action', 'remote' => ['action']],
        'iframe' => ['id' => 'frame-src', 'remote' => ['src']],
        'frame' => ['id' => 'frame-src', 'remote' => ['src']]
    ];

    private const VOID_ELEMENTS_MAP = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'command' => true,
        'embed' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'keygen' => true,
        'link' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerFactory;

    protected $escaper;

    /**
     * Data constructor.
     * @param ProductMetadataInterface $productMetadata
     * @param Context $context
     * @param CollectionFactory $customerFactory
     * @param Escaper $escaper
     * @param Random $random
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Framework\Escaper $escaper,
        Random $random
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->escaper=$escaper;
        $this->random=$random;
    }

    /**
     * Return information array of customer attribute input types
     *
     * @param string $inputType
     * @return array
     */
    public function getAttributeInputTypes($inputType = null)
    {
        $inputTypes = [
            'file' => [
                'validate_types' => ['max_file_size', 'file_extensions','default_value_required']
            ],
            'multiselect' => [
                'validate_types' => [],
                'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class
            ],
            'radio' => [
                'validate_types' => [],
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'data_model' => \Bss\CustomerAttributes\Model\Metadata\Form\Radio::class
            ],
            'checkboxs' => [
                'validate_types' => [],
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'data_model' => \Bss\CustomerAttributes\Model\Metadata\Form\CheckBoxs::class
            ],
            'boolean' => [
                'validate_types' => [],
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class
            ],
            'date' => [
                'label' => __('Date'),
                'manage_options' => false,
                'validate_types' => ['default_value_required'],
                'validate_filters' => ['date'],
                'filter_types' => ['date'],
                'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
                'backend_type' => 'datetime',
                'default_value' => 'date',
            ],
            'text' => [
                'validate_types' => ['default_value_required']
            ],
            'yesno' => [
                'validate_types' => ['default_value_required']
            ],
            'textarea' => [
                'validate_types' => ['default_value_required']
            ],
        ];

        if ($inputType === null) {
            return $inputTypes;
        } else {
            if (isset($inputTypes[$inputType])) {
                return $inputTypes[$inputType];
            }
        }
        return [];
    }

    /**
     * Return default attribute backend model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeBackendModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();

        if (!empty($inputTypes[$inputType]['backend_model'])) {
            return $inputTypes[$inputType]['backend_model'];
        }
        return null;
    }

    /**
     * Return default attribute source model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeSourceModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['source_model'])) {
            return $inputTypes[$inputType]['source_model'];
        }
        return null;
    }

    /**
     * Return default attribute data model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeDataModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['data_model'])) {
            return $inputTypes[$inputType]['data_model'];
        }
        return null;
    }

    /**
     * Return Validate Rules by input type
     *
     * @param string $inputType
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function getAttributeValidateRules($inputType, array $data)
    {
        $inputTypes = $this->getAttributeInputTypes();
        $rules = [];
        if (isset($inputTypes[$inputType])) {
            foreach ($inputTypes[$inputType]['validate_types'] as $validateType) {
                if (!empty($data[$validateType])) {
                    $rules[$validateType] = $data[$validateType];
                } elseif (!empty($data['scope_' . $validateType])) {
                    $rules[$validateType] = $data['scope_' . $validateType];
                }
            }

            if ($inputType === 'date') {
                $rules['input_validation'] = 'date';
            }
        }

        return $rules;
    }

    /**
     * Get Backend Input Type
     *
     * @param string $type
     * @return null|string
     */
    public function getBackendTypeByInput($type)
    {
        $field = null;
        if ($this->returnVarChar($type)) {
            $field = 'varchar';
            return $field;
        } elseif ($type == 'textarea') {
            $field = 'text';
            return $field;
        } elseif ($type == 'date') {
            $field = 'datetime';
            return $field;
        } elseif ($type == 'select' || $type == 'radio' || $type == 'boolean') {
            $field = 'int';
            return $field;
        } else {
            return $field;
        }
    }

    /**
     * @param string $type
     * @return bool
     */
    private function returnVarChar($type)
    {
        if ($type == 'text' || $type == 'multiselect' || $type == 'checkboxs' || $type == 'file') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Default Value by Input Type
     *
     * @param string $type
     * @param string $required
     * @return string|null
     */
    public function getDefaultValueByInput($type, $required = null)
    {
        $field = '';
        $arrTypeSelect = ['select','radio'];
        $arrDefault = ['text','textarea','boolean','file', 'date'];
        $arrMulti = ['multiselect','checkboxs'];
        if (in_array($type, $arrTypeSelect)) {
            return $field;
        } elseif (in_array($type, $arrDefault)) {
            if ($type == 'boolean') {
                return 'default_value_yesno' . $required;
            } else {
                return 'default_value_' . $type . $required;
            }
        } elseif (in_array($type, $arrMulti)) {
            return null;
        }
        return $field;
    }

    /**
     * Get Config
     *
     * @param string $path
     * @param int $store
     * @param string $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Check module is enable or
     *
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getConfig('bss_customer_attribute/general/enable');
    }

    /**
     * Check is allow set default required attribute for existing customer
     *
     * @return string
     */
    public function isAllowSetDefaultConfig()
    {
        return $this->getConfig('bss_customer_attribute/general/set_required_attribute');
    }
    /**
     * Check customer attribute dependency is enable or
     *
     * @return mixed
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->getConfig('bss_customer_attribute/general/enable_customer_attribute_dependency');
    }
    /**
     * Check customer attribute dependency is enable or
     *
     * @return mixed
     */
    public function displayChildValues()
    {
        return 0;
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    public function returnCustomerFactory()
    {
        return $this->customerFactory;
    }

    /**
     * Check version magento
     *
     * @return string
     */
    public function checkVersionMagento()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Same 100% core
     *
     * @param string $style
     * @param string $selector
     * @return string
     * @throws LocalizedException
     */
    public function renderStyleAsTag($style, $selector)
    {
        $stylePairs = array_filter(array_map('trim', explode(';', $style)));
        if (!$stylePairs || !$selector) {
            throw new \InvalidArgumentException('Invalid style data given');
        }

        $elementVariable = 'elem' . $this->random->getRandomString(8);
        /** @var string[] $styles */
        $stylesAssignments = '';
        foreach ($stylePairs as $stylePair) {
            $exploded = array_map('trim', explode(':', $stylePair));
            if (count($exploded) < 2) {
                throw new \InvalidArgumentException('Invalid CSS given');
            }
            //Converting to camelCase
            $styleAttribute = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $exploded[0]))));
            if (count($exploded) > 2) {
                //For cases when ":" is encountered in the style's value.
                $exploded[1] = join('', array_slice($exploded, 1));
            }
            $styleValue = str_replace('\'', '\\\'', trim($exploded[1]));
            $stylesAssignments .= "$elementVariable.style.$styleAttribute = '$styleValue';\n";
        }

        return $this->renderTag(
            'script',
            ['type' => 'text/javascript'],
            "var $elementVariable = document.querySelector('$selector');\n"
            . "if ($elementVariable) {\n{$stylesAssignments}}",
            false
        );
    }

    /**
     * Same 100% core
     *
     * @param string $eventName
     * @param string $attributeJavascript
     * @param string $elementSelector
     * @return string
     * @throws LocalizedException
     */
    public function renderEventListenerAsTag(
        $eventName,
        $attributeJavascript,
        $elementSelector
    ) {
        if (!$eventName || !$attributeJavascript || !$elementSelector || mb_strpos($eventName, 'on') !== 0) {
            throw new \InvalidArgumentException('Invalid JS event handler data provided');
        }
        $random = $this->random->getRandomString(10);
        $listenerFunction = 'eventListener' . $random;
        $elementName = 'listenedElement' . $random;
        $script = <<<script
            function {$listenerFunction} () {
                {$attributeJavascript};
            }
            var {$elementName}Array = document.querySelectorAll("{$elementSelector}");
            if({$elementName}Array.length !== 'undefined'){
                {$elementName}Array.forEach(function(element) {
                    if (element) {
                        element.{$eventName} = function (event) {
                            var targetElement = element;
                            if (event && event.target) {
                                targetElement = event.target;
                            }
                            return {$listenerFunction}.apply(targetElement);
                        };
                    }
                });
            }
        script;

        return $this->renderTag('script', ['type' => 'text/javascript'], $script);
    }

    /**
     * Same 100% core
     *
     * @param string $tagName
     * @param array $attributes
     * @param string|null $content
     * @param bool|null $textContent
     * @return string
     */
    public function renderTag($tagName, $attributes, $content = null, $textContent = true)
    {
        if (!array_key_exists($tagName, self::$tagMeta)) {
            throw new \InvalidArgumentException('Unknown source type - ' .$tagName);
        }

        $attributesHtmls = [];
        foreach ($attributes as $attribute => $value) {
            $attributesHtmls[] = $attribute . '="' .$this->escaper->escapeHtmlAttr($value) .'"';
        }
        if ($content !== null) {
            $content = $textContent ? $this->escaper->escapeHtml($content) : $content;
        }
        $attributesHtml = '';
        if ($attributesHtmls) {
            $attributesHtml = ' ' .implode(' ', $attributesHtmls);
        }

        $html = '<' .$tagName .$attributesHtml;
        if (isset(self::VOID_ELEMENTS_MAP[$tagName])) {
            $html .= '/>';
        } else {
            $html .= '>' .$content .'</' .$tagName .'>';
        }

        return $html;
    }
}
