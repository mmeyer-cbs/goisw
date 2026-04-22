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
namespace Bss\QuoteExtension\Model\Pdf\Config;

use Magento\Framework\Module\Dir;

/**
 * Class SchemaLocator
 * Attributes config schema locator
 *
 * @package Bss\QuoteExtension\Model\Pdf\Config
 * @codingStandardsIgnoreFile
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
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $dir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Bss_QuoteExtension');
        $this->schema = $dir . '/quote_extension_pdf.xsd';
        $this->schemaFile = $dir . '/quote_extension_pdf_file.xsd';
    }

    /**
     * { @inheritdoc }
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * { @inheritdoc }
     */
    public function getPerFileSchema()
    {
        return $this->schemaFile;
    }
}
