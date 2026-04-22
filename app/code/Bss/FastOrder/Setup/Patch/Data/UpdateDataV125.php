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
 * @category  BSS
 * @package   Bss_FastOrder
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\FastOrder\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Data patch format
 */
class UpdateDataV125 implements DataPatchInterface
{
    /**
     * @param \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * AccountPurposeCustomerAttribute constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->setup = $setup;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Update table cms_page.
     *
     * @return UpdateDataV125|void
     * @throws \Exception
     */
    public function apply()
    {
        $setup = $this->setup;
        $setup->startSetup();

        $checkBlockExists = 0;
        $pageCollection = $this->pageFactory->create()->getCollection();
        foreach ($pageCollection as $item) {
            $content = $item->getContent();
            if ($content && strpos($content, 'Bss\FastOrder\Block\FastOrder') !== false) {
                $checkBlockExists = 1;
                break;
            }
        }

        if ($checkBlockExists == 0) {
            $cmsPageData = [
                'title' => __('Fast Order'),
                'page_layout' => '1column',
                'meta_keywords' => 'Fast order',
                'meta_description' => 'Fast order',
                'identifier' => 'fast-order',
                'content_heading' => 'Fast order',
                'content' => '{{block class="Bss\FastOrder\Block\FastOrder" template="Bss_FastOrder::fastorder.phtml"}}',
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0,
                'bss_redirect_type' => '',
                'bss_select_page' => '',
                'bss_custom_url' => '',
                'bss_error_message' => ''
            ];
            $this->pageFactory->create()->addData($cmsPageData)->save();
        }

        $setup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Compare ver module.
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.2.5';
    }
}
