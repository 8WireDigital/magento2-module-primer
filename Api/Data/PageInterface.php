<?php

namespace EightWire\Primer\Api\Data;

/**
 * Page interface.
 * @api
 */
interface PageInterface
{
    const PAGE_ID = 'page_id';


    const PATH = 'path';

    const STATUS = 'status';

    const PRIORITY = 'priority';

    const CREATED_AT = 'created_at';


    const UPDATED_AT = 'updated_at';

    const STORE_ID = 'store_id';


    /**
     * Gets the ID for the page.
     *
     * @return int|null page ID.
     */
    public function getPageId();

    /**
     * Sets page ID.
     *
     * @param int $pageId
     * @return $this
     */
    public function setPageId($pageId);


    /**
     * Gets the path for the page.
     *
     * @return string
     */
    public function getPath();

    /**
     * Sets page path
     *
     * @param int $path
     * @return $this
     */
    public function setPath($path);


    /**
     * Gets the status for the page.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Sets page status.
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Gets the priority for the page.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Sets page priority.
     *
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority);


    /**
     * Gets the page created-at timestamp.
     *
     * @return string|null Credit memo created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the page created-at timestamp.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);


    /**
     * Gets the page updated-at timestamp.
     *
     * @return string|null Credit memo created-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Sets the page updated-at timestamp.
     *
     * @param string $updatedAt timestamp
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Gets the store ID for the page.
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Sets store ID.
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);
}
