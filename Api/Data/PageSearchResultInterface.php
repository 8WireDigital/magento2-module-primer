<?php

namespace EightWire\Primer\Api\Data;

/**
 * page search result interface.
 *
 * @api
 */
interface PageSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \EightWire\Primer\Api\Data\PageInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \EightWire\Primer\Api\Data\PageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
