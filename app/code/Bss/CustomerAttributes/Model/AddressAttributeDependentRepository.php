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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\CustomerAttributes\Model;

use Bss\CustomerAttributes\Api\AddressAttributeRepositoryInterface;
use Bss\CustomerAttributes\Api\Data\AddressAttributeInterface;
use Bss\CustomerAttributes\Model\ResourceModel\AddressAttribute\AddressAttributeDependent as AddressAttributeDependent;

class AddressAttributeDependentRepository implements AddressAttributeRepositoryInterface
{
    public const ATTR_ID = 'attr_id';
    /**
     * @var AddressAttributeDependentFactory
     */
    protected $addAttributeDependent;

    /**
     * @var AddressAttributeDependent
     */
    protected $resourceAddAttributeDependent;

    /**
     * @param AddressAttributeDependentFactory $addAttributeDependent
     * @param AddressAttributeDependent $resourceAddAttributeDependent
     */
    public function __construct(
        AddressAttributeDependentFactory $addAttributeDependent,
        AddressAttributeDependent        $resourceAddAttributeDependent
    ) {
        $this->addAttributeDependent = $addAttributeDependent;
        $this->resourceAddAttributeDependent = $resourceAddAttributeDependent;
    }

    /**
     * Create Model
     *
     * @return \Bss\CustomerAttributes\Api\Data\AddressAttributeInterface
     */
    public function create()
    {
        return $this->addAttributeDependent->create();
    }

    /**
     * Get Data by id
     *
     * @param int $id
     * @return \Bss\CustomerAttributes\Api\Data\AddressAttributeInterface
     */
    public function load($id)
    {
        $sampleModel = $this->create();
        $this->resourceAddAttributeDependent->load($sampleModel, $id);
        return $sampleModel;
    }

    /**
     * Get Data by attr_id
     *
     * @param int $attrId
     * @return \Bss\CustomerAttributes\Api\Data\AddressAttributeInterface
     */
    public function getDataByAttrID($attrId)
    {
        $sampleModel = $this->create();
        $this->resourceAddAttributeDependent->load($sampleModel, $attrId, self::ATTR_ID);
        return $sampleModel;
    }
}
