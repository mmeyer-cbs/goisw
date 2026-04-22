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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Controller\File;

use Bss\CustomerAttributes\Helper\Customerattribute as HelperCustomerAttribute;
use Bss\CustomerAttributes\Model\Metadata\Form\File as MetadataFormFile;
use Exception;
use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttribute;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Result\Layout;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Class for upload files for customer custom address attributes
 * @SuppressWarnings(PHPMD)
 */
class Uploader extends Action
{
    public const PATH_SAVE_FILE_FOLDER = "customer/bss/customerattributes/";
    public const PATH_SAVE_FILE_DATABASE = "/bss/customerattributes/";
    /**
     * @var HelperCustomerAttribute
     */
    protected $helperCustomerAttribute;
    /**
     * @var CustomerAttribute
     */
    protected $customerAttribute;
    /**
     * @var EavAttribute
     */
    protected $eavAttribute;
    /**
     * @var MetadataFormFile
     */
    protected $metadataFormFile;

    /**
     * Media directory object (writable).
     *
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @param HelperCustomerAttribute $helperCustomerAttribute
     * @param CustomerAttribute $customerAttribute
     * @param EavAttribute $eavAttribute
     * @param MetadataFormFile $metadataFormFile
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Context $context
     * @throws FileSystemException
     */
    public function __construct(
        HelperCustomerAttribute $helperCustomerAttribute,
        CustomerAttribute $customerAttribute,
        EavAttribute $eavAttribute,
        MetadataFormFile $metadataFormFile,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        Context $context
    ) {
        $this->helperCustomerAttribute = $helperCustomerAttribute;
        $this->customerAttribute = $customerAttribute;
        $this->eavAttribute = $eavAttribute;
        $this->metadataFormFile = $metadataFormFile;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        parent::__construct($context);
    }

    /**
     * Upload and download file
     *
     * @return ResponseInterface|ResultInterface|Layout
     * @throws Exception
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $bssCustomerAttributes = $this->getRequest()->getFiles('bss_customer_attributes');
            if (!$bssCustomerAttributes) {
                $result["error"] = "The file size should not exceed " .
                    $this->helperCustomerAttribute->getMaxFileSizePhp() . "KB";
                $resultJson->setData($result);
                return $resultJson;
            }
            $codeAttribute = "";
            $attributeFile = [];
            foreach ($bssCustomerAttributes as $key => $value) {
                $codeAttribute = $key;
                $attributeFile = $value;
                break;
            }
            $idAttribute = $this->eavAttribute->getIdByCode(Customer::ENTITY, $codeAttribute);
            $dataAttribute = $this->customerAttribute->load($idAttribute);
            $validateFile = $this->metadataFormFile->validateByRules($dataAttribute, $attributeFile);
            if ($validateFile) {
                if (isset($validateFile["protectedExtension"])) {
                    $result["error"] = $validateFile["protectedExtension"];
                } else {
                    $result["error"] = $validateFile;
                }
                $resultJson->setData($result);
                return $resultJson;
            }
            $uploader = $this->uploaderFactory->create(['fileId' => $attributeFile]);
            $uploader->setFilesDispersion(false);
            $uploader->setFilenamesCaseSensitivity(false);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($this->mediaDirectory->getAbsolutePath(self::PATH_SAVE_FILE_FOLDER));
            $result["filedValue"] = self::PATH_SAVE_FILE_DATABASE . $result["file"];
            $result["url"] = $this->helperCustomerAttribute->getViewFile($result["filedValue"]);
            $result["bss_customer_attributes"] = $codeAttribute;
            $resultJson->setData($result);
        } catch (Exception $exception) {
            $result["error"] = $exception->getMessage();
        }
        return $resultJson;
    }

    /**
     * Format name file
     *
     * @param string $nameFile
     * @return string|string[]|null
     */
    public function renameFile($nameFile)
    {
        return str_replace(
            " ",
            "_",
            preg_replace('/[^a-zA-Z0-9_.]/s', '', $nameFile)
        );
    }
}
