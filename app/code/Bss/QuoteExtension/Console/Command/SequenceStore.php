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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SequenceStore
 */
class SequenceStore extends Command
{
    /**
     * @var \Bss\QuoteExtension\Model\SequenceStore
     */
    protected $sequenceStore;

    /**
     * Constructor.
     *
     * @param \Bss\QuoteExtension\Model\SequenceStore $sequenceStore
     * @param string|null $name
     */
    public function __construct(
        \Bss\QuoteExtension\Model\SequenceStore $sequenceStore,
        string $name = null
    ) {
        $this->sequenceStore = $sequenceStore;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('quote_extension:sequence')
            ->setDescription('Quote Extension Sequence');
    }

    /**
     * Create sequence with metadata and profile: quoteExtension
     * When admin create storeView before disable module QuoteExtension
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->sequenceStore->addSequence();
    }
}
