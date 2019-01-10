<?php
namespace EightWire\Primer\Model;

use EightWire\Primer\Api\Data\PageSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use EightWire\Primer\Model\ResourceModel\Page as Resource;
//use Magento\Sales\Model\ResourceModel\Metadata;
//use Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class PageRepository implements \EightWire\Primer\Api\PageRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * \Magento\Sales\Api\Data\CreditmemoInterface[]
     *
     * @var array
     */
    protected $registry = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    private $pageRepository;

    /**
     * CreditmemoRepository constructor.
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        \EightWire\Primer\Model\PageFactory $pageFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->objectManager = $objectManager;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Loads a specified page
     *
     * @param int $id The page ID.
     * @return \EightWire\Primer\Api\Data\PageInterface Page Interface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var \EightWire\Primer\Api\Data\PageInterface $entity */
            $entity = $this->pageFactory->create()->load($id);

            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->registry[$id] = $entity;
        }

        return $this->registry[$id];
    }

    /**
     * Create page instance

     * @return Page
     */
    public function create()
    {
        return $this->pageFactory->create();
    }

    /**
     * Lists pages that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \EightWire\Primer\Api\Data\PageSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \EightWire\Primer\Model\ResourceModel\Page\Collection $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    /**
     * Deletes a specified page.

     * @param \EightWire\Primer\Api\Data\PageInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\EightWire\Primer\Api\Data\PageInterface $entity)
    {
        try {
            $entity->delete($entity);
            unset($this->registry[$entity->getEntityId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete page'), $e);
        }
        return true;
    }


    /**
     * Deletes a specified page by ID
     *
     * @param int $id
     * @return bool|void
     * @throws CouldNotDeleteException
     * @throws InputException
     */
    public function deleteById($id)
    {

        if (!$id) {
            throw new InputException(__('Id required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var \EightWire\Primer\Api\Data\PageInterface $entity */
            $entity = $this->pageFactory->create()->load($id);
            $this->registry[$id] = $entity;
        }

        $this->delete($this->registry[$id]);
    }


    /**
     * Performs persist operations for a specified credit memo.
     *
     * @param \EightWire\Primer\Api\Data\PageInterface $entity the page.
     * @return \EightWire\Primer\Api\Data\PageInterface page interface.
     * @throws CouldNotSaveException
     */
    public function save(\EightWire\Primer\Api\Data\PageInterface $entity)
    {
        try {
            $entity->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save page'), $e);
        }
        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 100.2.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }


    /**
     * Flush all pages within a collection so they can be crawled again
     *
     * @param null $collection
     */
    public function flush($collection = null)
    {
        if ($collection == null) {
            $search_criteria = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaInterface');
            $collection = $this->getList($search_criteria);
        }

        $collection->flushStatus();
    }
}
