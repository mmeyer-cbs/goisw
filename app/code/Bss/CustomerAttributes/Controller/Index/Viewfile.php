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
namespace Bss\CustomerAttributes\Controller\Index;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Url\DecoderInterface;
use Magento\MediaStorage\Helper\File\Storage;

/**
 * Class Viewfile
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Controller\Index
 */
class Viewfile extends \Magento\Framework\App\Action\Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var File
     */
    protected $file;

    /**
     * Viewfile constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param DecoderInterface $urlDecoder
     * @param Filesystem $filesystem
     * @param Storage $storage
     * @param FileFactory $fileFactory
     * @param File $file
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        DecoderInterface $urlDecoder,
        Filesystem $filesystem,
        Storage $storage,
        FileFactory $fileFactory,
        File $file
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->urlDecoder  = $urlDecoder;
        $this->filesystem = $filesystem;
        $this->storage = $storage;
        $this->fileFactory = $fileFactory;
        $this->file = $file;
    }

    /**
     * Customer view file action
     *
     * @return \Magento\Framework\Controller\Result\Raw|void
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        list($file, $plain) = $this->getFileParams();
        $directory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $directPath = $this->getDirectPath();
        if (!$directPath) {
            $directPath = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
        }
        $fileName = $directPath . '/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);
        if (mb_strpos($path, '..') !== false
            || (!$directory->isFile($fileName)
                && !$this->storage->processStorageFile($path))
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        $pathInfo = $this->file->getPathInfo($path);
        if ($plain) {
            $extension = $pathInfo["extension"];
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }
            $stat = $directory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($fileName));
            return $resultRaw;
        } else {
            $name = $pathInfo["basename"];
            $this->fileFactory->create(
                $name,
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }

    /**
     * Get parameters from request.
     *
     * @return array
     */
    private function getFileParams()
    {
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );

            return [$file, false];
        } elseif ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('image')
            );

            return [$file, true];
        } else {
            throw new NotFoundException(__('Page not found.'));
        }
    }

    /**
     * Get Direct Path
     *
     * @return false|mixed
     */
    private function getDirectPath()
    {
        if ($this->getRequest()->getParam('path')) {
            return $this->getRequest()->getParam('path');
        }
        return false;
    }
}
