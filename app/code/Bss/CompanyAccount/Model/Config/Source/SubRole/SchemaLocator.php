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

use Magento\Framework\Module\Dir;

/**
 * Class SchemaLocator
 *
 * Attributes config schema locator
 *
 * @package Bss\CompanyAccount\Model\Config\Source\SubRole;
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged configs
     *
     * @var string
     */
    private $schema;

    /**
     * Path to corresponding XSD file with validation rules for individual configs
     *
     * @var string
     */
    private $schemaFile;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param null $moduleName
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader, $moduleName = null)
    {
        if (!$moduleName) {
            $moduleName = "Bss_CompanyAccount";
        }
        $dir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, $moduleName);
        $this->schema = $dir . '/company_rules.xsd';
        $this->schemaFile = $dir . '/company_rules_file.xsd';
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @inheritdoc
     */
    public function getPerFileSchema()
    {
        return $this->schemaFile;
    }
}
