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
         * Things not to log
         *
         * Blacklisted User Agents - don't record pages being crawled by bots etc as they will skew results
         * Non Cached pages - whats the point crawling a page if its not going to be cached (how can I find this out reliably?)
         * Non whitelisted actions - we explicitly define which actions should be primed, if not defined we don't need to log it
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
                $page->setUpdatedAt(time());
            } else {
                $page = $this->pageRepository->create();
                $page->setPath($this->getPath($request));
                $page->setMagentoVary($request->getCookie('X-Magento-Vary'));
                $page->setStoreId($storeId);
                $page->setStatus(1);
                $page->setPriority(1);
                $page->setUpdatedAt(time());

                /**
                 * would be good if we could store cache tags here so that we can invalidate crawler by tag however
                 * X-Magento-Tags is unset on the header in Magento\Framework\App\PageCache\Kernel::process
                 */
            }
            $this->pageRepository->save($page);
        }
    }

    /**
     * Check a request object to see if we should log the page
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    private function shouldLogRequest(\Magento\Framework\App\Request\Http $request)
    {
        // this happens when returning a cached page
        if ($request->getFullActionName() == null) {
            return false;
        }

        // only log GET requests as thats all we crawl
        if ($request->getMethod() != "GET") {
            return false;
        }

        // @todo get these from configuration xml and further db config
        $blacklistAgents = [
            '/^Magento Primer Crawler$/',
            '/Googlebot/',
            '/UptimeRobot/'
        ];

        foreach ($blacklistAgents as $regex) {
            if (preg_match($regex, $request->getHeader('User-Agent'))) {
                return false;
            }
        }

        // @todo get these from configuration xml and further db config
        $blacklistParams = [
            'mc_id',
            'mc_eid',
            'SID',
            'utm_source',
            'utm_campaign',
            'utm_medium',
            'utm_term',
            'fbclid',
            'gclid',
            'emailoffers' //advintage only - to be removed and added through configuration
        ];

        foreach (array_keys($request->getParams()) as $parameterName) {
            if (in_array($parameterName, $blacklistParams)) {
                return false;
            }
        }
        

//        can't do this for now as cached pages don't have an action name
//        need to work out how to get this or only log non cached pages
        // performing logging only on non cached pages would solve this, its kind of a requirement with varnish anyway
        // this would mean changing the event we fire this on
        if (!$this->actionIsWhitelisted($request->getFullActionName())) {
            return false;
        }

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
     * We don't need to log every single page to get an accurate measure of what pages are most popular
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

    private function getPath($request)
    {
        return $request->getRequestString()?:'/';
    }


    /**
     * Check for existing logs matching current request
     *
     * @param $request
     * @return \EightWire\Primer\Api\Data\PageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function matchRequest($request)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $path = $this->getPath($request);

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
        
        $varyFilter = $this->objectManager->create('Magento\Framework\Api\Filter');
        $varyFilter->setData('field', 'magento_vary');

        if ($request->getCookie('X-Magento-Vary')) {
            $varyFilter->setData('value', $request->getCookie('X-Magento-Vary'));

        } else {
            $varyFilter->setData('condition_type', 'null');
        }

        $storeFilterGroup = $this->objectManager->create('Magento\Framework\Api\Search\FilterGroup');
        $storeFilterGroup->setData('filters', [$varyFilter]);

        $search_criteria = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaInterface');
        $search_criteria->setFilterGroups([$pathFilterGroup, $storeFilterGroup]);

        $result = $this->pageRepository->getList($search_criteria);

        return $result;
    }

    /**
     * Check getFullActionName of controller to see if it has been explicitly whitelisted
     *
     * @param $action
     * @return bool
     */
    private function actionIsWhitelisted($action)
    {
        /**
         * full action names to log
         *
         * @todo move to xml config so other modules can provide more actions
         */
        $controllerWhitelist = [
            'cms_index_index',
            'cms_page_view',
            'catalog_product_view',
            'catalog_category_view'
        ];

        return in_array($action, $controllerWhitelist);
    }
}
