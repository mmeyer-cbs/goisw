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

namespace Bss\CustomerAttributes\Model;

use Bss\CustomerAttributes\Api\AttributeRepositoryInterface;
use Bss\CustomerAttributes\Api\Data\AttributeInterface;
use Bss\CustomerAttributes\Model\ResourceModel\Attribute\AttributeDependent as AttributeDependent;

class AttributeDependentRepository implements AttributeRepositoryInterface
{
    public const ATTR_ID = 'attr_id';

    /**
     * @var AttributeDependentFactory
     */
    protected $attributeDependent;

    /**
     * @var AttributeDependent
     */
    protected $resourceAttributeDependent;

    /**
     * @param AttributeDependentFactory $attributeDependent
     * @param AttributeDependent $resourceAttributeDependent
     */
    public function __construct(
        AttributeDependentFactory $attributeDependent,
        AttributeDependent        $resourceAttributeDependent
    ) {
        $this->attributeDependent = $attributeDependent;
        $this->resourceAttributeDependent = $resourceAttributeDependent;
    }

    /**
     * Create Model
     *
     * @return \Bss\CustomerAttributes\Api\Data\AttributeInterface
     */
    public function create()
    {
        return $this->attributeDependent->create();
    }

    /**
     * Get Data by id
     *
     * @param int $id
     * @return \Bss\CustomerAttributes\Api\Data\AttributeInterface
     */
    public function load($id)
    {
        $sampleModel = $this->create();
        $this->resourceAttributeDependent->load($sampleModel, $id);
        return $sampleModel;
    }

    /**
     * Get Data by attr_id
     *
     * @param int $attrId
     * @return \Bss\CustomerAttributes\Api\Data\AttributeInterface
     */
    public function getDataByAttrID($attrId)
    {
        $sampleModel = $this->create();
        $this->resourceAttributeDependent->load($sampleModel, $attrId, self::ATTR_ID);
        return $sampleModel;
    }
}
