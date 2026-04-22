<?php

namespace Bss\CustomerAttributes\Model\Config\Source;

class ListMode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Option
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('At least one parent value is selected')],
            ['value' => '1', 'label' => __('All parent values are selected')],
        ];
    }
}
