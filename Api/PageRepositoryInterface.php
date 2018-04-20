<?php

namespace EightWire\Primer\Api;

/**
 * Page interface.
 * @api
 */
interface PageRepositoryInterface
{
    /**
     * Save page.
     *
     * @param \EightWire\Primer\Api\Data\PageInterface $page
     * @return \EightWire\Primer\Api\Data\PageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\EightWire\Primer\Api\Data\PageInterface $page);


    /**
     * Retrieve page.
     *
     * @param int $pageId
     * @return \EightWire\Primer\Api\Data\PageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($pageId);


    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \EightWire\Primer\Api\Data\PageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete page.
     *
     * @param \EightWire\Primer\Api\Data\PageInterface $page
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\EightWire\Primer\Api\Data\PageInterface $page);

    /**
     * Delete page by ID.
     *
     * @param int $pageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($pageId);
}
