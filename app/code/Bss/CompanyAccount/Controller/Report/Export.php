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

namespace Bss\CompanyAccount\Controller\Report;

use Bss\CompanyAccount\Block\Report\Filter;
use Bss\CompanyAccount\Block\Report\Index;
use Bss\CompanyAccount\Helper\Tabs as TabsOrder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Export
 *
 * @package Bss\CompanyAccount\Controller\Report
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Export extends \Magento\Framework\App\Action\Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Index
     */
    private $dataExport;

    /**
     * @var Filter
     */
    private $dataFilterExport;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var TabsOrder
     */
    protected $tabsHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Function Construct
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Csv $csvProcessor
     * @param DirectoryList $directoryList
     * @param Filter $dataFilterExport
     * @param Index $dataExport
     * @param Data $data
     * @param TabsOrder $tabsHelper
     * @param Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context       $context,
        FileFactory   $fileFactory,
        Csv           $csvProcessor,
        DirectoryList $directoryList,
        Filter        $dataFilterExport,
        Index         $dataExport,
        Data          $data,
        TabsOrder     $tabsHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->dataExport = $dataExport;
        $this->dataFilterExport = $dataFilterExport;
        $this->tabsHelper = $tabsHelper;
        $this->data = $data;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /**
     * Function execute Export
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $fileName = 'Orders-report.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;

        $exportData = $this->exportData();
        $this->csvProcessor
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData(
                $filePath,
                $exportData
            );

        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream'
        );
    }

    /**
     * Function get export data
     *
     * @param \Magento\Customer\Model\Customer $data
     * @return array
     * @throws \Exception
     */
    protected function exportData()
    {
        $result[] = [
            'Ordinal Number',
            'User ID',
            'Name',
            'Email',
            'Total Order Value',
            'Currency Code',
            'Number of Orders',
            'Created Date',
        ];
        $dataExport = $this->dataFilterExport->getItems();
        /** @var \Bss\CompanyAccount\Api\Data\SubUserOrderInterface $user */
        $count = 1;
        foreach ($dataExport as $user) {
            $result[] = [
                $count,
                $user['user_id'] = $user->getSubId(),
                $user['sub_name'] = $user->subUser()->getSubName(),
                $user['sub_email'] = $user->subUser()->getSubEmail(),
                $user['grand_total'] = utf8_decode((string)$this->data->currency($user->getGrandTotal(), false, false)),
                $user['currency_code'] = $this->priceCurrency->getCurrency()->getCurrencyCode(),
                $user['count'] = $user->getCount(),
                $user['created_at'] = $this->tabsHelper->getFormatDate($user->getCreatedAt()),
            ];
            $count++;
        }
        return $result;
    }
}
