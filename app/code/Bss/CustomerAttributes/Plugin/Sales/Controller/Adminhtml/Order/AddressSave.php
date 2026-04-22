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
namespace Bss\CustomerAttributes\Plugin\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;

/**
 * Class AddressSave
 */
class AddressSave
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    public $helperCustomerAttributes;


    /**
     * AddressSave constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttributes
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttributes
    ) {
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->helperCustomerAttributes = $helperCustomerAttributes;
    }

    /**
     * Download file and setValueParam of custom address attributes
     *
     * @param \Magento\Sales\Controller\Adminhtml\Order\AddressSave $subject
     * @return \Magento\Sales\Controller\Adminhtml\Order\AddressSave
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute($subject)
    {
        try {
            $mediaDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            $customAddressAttributes = $this->helperCustomerAttributes->converAddressCollectioin();
            $files = $subject->getRequest()->getFiles();
            if (count($files)) {
                foreach ($files->toArray() as $name => $file) {
                    if (isset($customAddressAttributes[$name])) {
                        $uploader = $this->uploaderFactory->create(['fileId' => $file]);
                        $uploader->setFilesDispersion(true);
                        $uploader->setFilenamesCaseSensitivity(false);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->save($mediaDir->getAbsolutePath("customer_address"), $file['name']);
                        $nameFile = $uploader->getUploadedFileName();
                        $subject->getRequest()->setParam($name, ["value" => $nameFile]);
                        $subject->getRequest()->setPostValue($name, ["value" => $nameFile]);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $subject;
    }

}
