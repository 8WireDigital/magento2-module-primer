<?php
namespace EightWire\Primer\Model;

class PageLogger
{

    private $pageRepository;

    private $storeManager;

    private $objectManager;

    public function __construct(
        \EightWire\Primer\Api\PageRepositoryInterface $pageRepository,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager

    ) {
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
    }

    public function log(\Magento\Framework\App\Request\Http $request, \Magento\Framework\App\Response\Http $response)
    {
        /**
         * @todo Things not to log
         *
         * Blacklisted User Agents - don't record pages being crawled by bots etc as they will skew results
         * Non Cached pages - whats the point crawling a page if its not going to be cached (how can I find this out reliably?)
         * Blacklisted Controllers - we can force block certain pages
         * Non 200 responses - don't want to be crawling pages that 301 redirect
         * Requests other than GET - as a crawler can't replicate them without all the data and they are likely user specifc anyway
         * URLS with obvious tracking parameters - will likely be unique per visitor e.g mailchimp etc
         * Apply Sample so only 1 in 10 log for example
         */
        if ($this->shouldLogRequest($request)
            && $this->shouldLogResponse($response)
            && $this->inSample()
        ) {
            $result = $this->matchRequest($request);
            $storeId = $this->storeManager->getStore()->getId();

            if ($result->getTotalCount()) {
                $page = $result->getFirstItem();
                $page->incrementPriority();
            } else {
                $page = $this->pageRepository->create();
                $page->setPath($request->getRequestUri());
                $page->setStoreId($storeId);
                $page->setStatus(0);
                $page->setPriority(1);
            }
            $this->pageRepository->save($page);
        }
    }

    /**
     * Check a request object to see if we should log the page
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    private function shouldLogRequest(\Magento\Framework\App\Request\Http $request)
    {
        if ($request->getMethod() != "GET") {
            return false;
        }

        if ($request->getHeader('User-Agent') != 'Magento Primer Crawler') {
            return false;
        }
//        can't do this for now as cached pages don't have an action name
//        need to work out how to get this or only log non cached pages
//        if ($this->actionIsBlacklisted($request->getFullActionName())) {
//            return false;
//        }

        return true;
    }


    /**
     * Check a response object to see if we should log the page
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return bool
     */
    private function shouldLogResponse(\Magento\Framework\App\Response\Http $response)
    {
        if ($response->getHttpResponseCode() != 200) {
            return false;
        }

        return true;
    }

    /**
     * We don't need to sample every single page to get an accurate measure of what pages are most popular
     * the purpose of this function is to only trigger recording the url on a configurable sample of pages
     *
     * e.g 1 in every 10 page views
     *
     * @return bool
     */
    private function inSample()
    {
        return true;
    }


    /**
     *
     * @param $request
     * @return \EightWire\Primer\Api\Data\PageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function matchRequest($request)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $path = $request->getRequestUri();


        $pathFilter = $this->objectManager->create('Magento\Framework\Api\Filter');
        $pathFilter->setData('field', 'path');
        $pathFilter->setData('value', $path);

        $pathFilterGroup = $this->objectManager->create('Magento\Framework\Api\Search\FilterGroup');
        $pathFilterGroup->setData('filters', [$pathFilter]);


        $storeFilter = $this->objectManager->create('Magento\Framework\Api\Filter');
        $storeFilter->setData('field', 'store_id');
        $storeFilter->setData('value', $storeId);

        $storeFilterGroup = $this->objectManager->create('Magento\Framework\Api\Search\FilterGroup');
        $storeFilterGroup->setData('filters', [$storeFilter]);


        $search_criteria = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaInterface');
        $search_criteria->setFilterGroups([$pathFilterGroup, $storeFilterGroup]);

        $result = $this->pageRepository->getList($search_criteria);

        return $result;
    }

    /**
     * Check getFullActionName of controller to see if it has been explicitly blacklisted
     *
     * @param $action
     * @return bool
     */
    private function actionIsBlacklisted($action)
    {
        /**
         * full action names to skip
         *
         * @todo get full list of actions that will never be cached
         * @todo move to xml config so other modules can provide more actions
         */
        $skipcontrollers = [
            'cms_noroute_index',
            'customer_section_load',
            'customer_account_create',
            'customer_account_login',
            'search_ajax_suggest',
            'page_cache_block_render'
        ];

        var_dump($action);

        return in_array($action, $skipcontrollers);
    }
}
