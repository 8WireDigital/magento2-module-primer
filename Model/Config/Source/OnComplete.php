<?php

namespace EightWire\Primer\Model\Config\Source;

use EightWire\Primer\Api\CrawlerInterface;

class OnComplete implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => CrawlerInterface::WHEN_COMPLETE_SLEEP, 'label' => __('Sleep')],
            ['value' => CrawlerInterface::WHEN_COMPLETE_STOP, 'label' => __('Stop')],
        ];
    }
}
