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

namespace Bss\CompanyAccount\Model\Config\Source\SubRole;

use Bss\CompanyAccount\Exception\RequiredAttributePermission;

/**
 * Class Converter
 *
 * Converter of pdf configuration from \DOMDocument to array
 *
 * @package Bss\CompanyAccount\Model\Config\Source\SubRole
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \Exception
     */
    public function convert($source)
    {
        $result = [];

        $xpath = new \DOMXPath($source);
        /** @var $resourceNode \DOMNode */
        foreach ($xpath->query('/config/acl/rules/rule') as $resourceNode) {
            $result['rules'][] = $this->_convertRuleNode($resourceNode);
        }
        return $result;
    }

    /**
     * Convert resource node into assoc array
     *
     * @param \DOMNode $resourceNode
     * @return array
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _convertRuleNode(\DOMNode $resourceNode)
    {
        $ruleData = [];
        $resourceAttributes = $resourceNode->attributes;
        $valueNote = $resourceAttributes->getNamedItem('value');
        if ($valueNote === null) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new RequiredAttributePermission(__('Attribute "value" is required for ACL resource.'));
        }
        $ruleData['value'] = $valueNote->nodeValue;

        $titleNode = $resourceAttributes->getNamedItem('title');
        if ($titleNode === null) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new RequiredAttributePermission(__('Attribute "title" is required for ACL resource.'));
        }
        $isTranslate = $resourceAttributes->getNamedItem('translate');
        if ($isTranslate) {
            if ($isTranslate->nodeValue == "true") {
                $ruleData['label'] = __($titleNode->nodeValue);
            } else {
                $ruleData['label'] = $titleNode->nodeValue;
            }
        } else {
            $ruleData['label'] = $titleNode->nodeValue;
        }
        $removeAttr = $resourceAttributes->getNamedItem('remove');

        if ($removeAttr && $removeAttr->nodeValue == "true") {
            $ruleData['remove'] = true;
        }

        // convert child resource nodes if needed
        $ruleData['children'] = [];
        /** @var $childNode \DOMNode */
        foreach ($resourceNode->childNodes as $childNode) {
            if ($childNode->nodeName == 'rule') {
                $ruleData['children'][] = $this->_convertRuleNode($childNode);
            }
        }
        return $ruleData;
    }
}
