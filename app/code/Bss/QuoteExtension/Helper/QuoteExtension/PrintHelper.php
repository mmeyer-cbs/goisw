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
namespace Bss\QuoteExtension\Helper\QuoteExtension;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Bss\QuoteExtension\Model\Pdf\PrintPdf;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class MoveToQuote
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 */
class PrintHelper
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var PrintPdf
     */
    protected $modelPdf;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * PrintHelper constructor.
     * @param FileFactory $fileFactory
     * @param PrintPdf $modelPdf
     * @param DateTime $dateTime
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        FileFactory $fileFactory,
        PrintPdf $modelPdf,
        DateTime $dateTime,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->fileFactory = $fileFactory;
        $this->modelPdf = $modelPdf;
        $this->dateTime = $dateTime;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Get Quote Repository
     *
     * @return CartRepositoryInterface
     */
    public function getQuoteRepository()
    {
        return $this->quoteRepository;
    }

    /**
     * Get File Factory
     *
     * @return FileFactory
     */
    public function getFileFactory()
    {
        return $this->fileFactory;
    }

    /**
     * Get Model Pdf
     *
     * @return PrintPdf
     */
    public function getPrintPdf()
    {
        return $this->modelPdf;
    }

    /**
     * Get DateTime
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
