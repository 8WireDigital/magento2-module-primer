<?php
namespace EightWire\Primer\Model;

use EightWire\Primer\Api\CrawlerInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

class Crawler implements CrawlerInterface
{

    private $storeManager;

    private $objectManager;

    private $scopeConfig;

    private $queue;

    private $pageRepository;

    private $sleepBetweenBatch;
    private $sleepWhenEmpty;
    private $batchSize;
    private $maxRunTime;
    private $crawlThreshold;
    private $whenComplete;

    private $output;

    private $client;


    /**
     * Crawler constructor.
     * @param \EightWire\Primer\Api\PageRepositoryInterface $pageRepository
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \EightWire\Primer\Api\PageRepositoryInterface $pageRepository,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \EightWire\Primer\Helper\Config $configHelper
    ) {
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->cacheManager = $cacheManager;
        $this->configHelper = $configHelper;

        $this->client = new Client([
            'verify' => false,
            'headers'         => [
                'User-Agent' => 'Magento Primer Crawler',
            ],
        ]);
    }

    /**
     * Main run method
     */
    public function run()
    {
        if (!$this->cacheEnabled()) {
            $this->writeln('<info>Not running crawler as full page cache is disabled</info>');
            return;
        }
        $starttime = time();

        while (true) {
            $this->getNextBatch();

            // if we have no items in queue either stop process or sleep depending on config
            if (count($this->queue) < 1) {
                if ($this->getWhenComplete() != self::WHEN_COMPLETE_SLEEP) {
                    $this->writeln('<info>No pages in queue - exiting</info>');
                    return;
                }

                $this->writeln('<info>No pages in queue - waiting '.$this->getSleepWhenEmpty().' seconds</info>');
                sleep($this->getSleepWhenEmpty()); // @codingStandardsIgnoreLine
                continue;
            }

            $this->writeln('<info>Crawling '.count($this->queue).' pages</info>');

            if ($this->shouldPurge()) {
                $this->purge();
            }
            
            $this->prime();

            //stop crawler after max run time elapsed
            $runtime = time() - $starttime;
            if ($this->getMaxRunTime() > 0 && $runtime > $this->getMaxRunTime()) {
                $this->writeln('<info>Max runtime elapsed - exiting</info>');
                return;
            }

            sleep($this->getSleepBetweenBatch()); // @codingStandardsIgnoreLine
        }
    }

    /**
     * Send PURGE request all urls in queue
     */
    private function purge()
    {
        $promises = [];

        foreach ($this->queue as $page) {
            $url = $page->getStoreUrl();

            $sendtime = microtime(true);

            $request = new Request('PURGE', $url);

            $promises[] = $this->client->sendAsync($request)->then(
                function (Response $response) use ($page, $sendtime, $request) {
                    $responsetime = microtime(true);
                    $this->writeln('<info>PURGE '.$page->getPath() .'</info> <comment>'.$response->getStatusCode().', '.number_format (( $responsetime - $sendtime ), 2).'s</comment>');
                },
                function (RequestException $e) use ($page) {
                    $this->writeln('<info>PURGE '.$page->getPath() .'</info> <error> FAILED </error> 
<comment>'.$e->getMessage().'</comment>');
                }
            );
        }

        \GuzzleHttp\Promise\all($promises)->wait();
    }

    /**
     * Send GET request all urls in queue
     */
    private function prime()
    {
        $promises = [];

        foreach ($this->queue as $page) {
            $url = $page->getStoreUrl();

            $options = [];

            if ($page->getMagentoVary() != null) {
                $options['cookies'] = CookieJar::fromArray([
                    'X-Magento-Vary' => $page->getMagentoVary()
                ], $page->getCookieDomain());
            }

            $sendtime = microtime(true);

            $request = new Request('GET', $url);

            $promises[]  = $this->client->sendAsync($request, $options)->then(

                function (Response $response) use ($page, $sendtime, $request) {

                    $responsetime = microtime(true);

                    $this->writeln(
                        '<info>GET   '.$page->getPath() .' '.$page->getMagentoVary().'</info> <comment>'.$response->getStatusCode().', '.number_format (( $responsetime - $sendtime ), 2).'s</comment>'
                    );
                    $page->setStatus(1);
                    $this->pageRepository->save($page);
                }
            )->otherwise(function (\Exception $e) use ($page, $sendtime, $request) {
                $this->writeln(
                    '<error>'.$e->getMessage().'</error>'
                );
                $priority = $page->getPriority();
                $page->setPriority($priority-1);
                $page->setStatus(1);
                $this->pageRepository->save($page);
            });

        }

        \GuzzleHttp\Promise\all($promises)->wait();
    }

    /**
     * Update queue with next batch of urls to process
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getNextBatch()
    {

        $statusFilter = $this->objectManager->create('Magento\Framework\Api\Filter');
        $statusFilter->setData('field', 'status');
        $statusFilter->setData('value', 0);
        $statusFilter->setData('condition_type', 'eq');

        $statusFilterGroup = $this->objectManager->create('Magento\Framework\Api\Search\FilterGroup');
        $statusFilterGroup->setData('filters', [$statusFilter]);


        $priorityFilter = $this->objectManager->create('Magento\Framework\Api\Filter');
        $priorityFilter->setData('field', 'priority');
        $priorityFilter->setData('value', $this->getCrawlThreshold()());
        $priorityFilter->setData('condition_type', 'gteq');

        $priorityFilterGroup = $this->objectManager->create('Magento\Framework\Api\Search\FilterGroup');
        $priorityFilterGroup->setData('filters', [$priorityFilter]);


        $sortOrder = $this->objectManager->create('Magento\Framework\Api\SortOrder');
        $sortOrders = [
            $sortOrder->setField('priority')->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC)
        ];

        /** @var \Magento\Framework\Api\SearchCriteriaInterface $search_criteria */
        $search_criteria = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaInterface');
        $search_criteria ->setFilterGroups([$statusFilterGroup, $priorityFilterGroup])
                         ->setPageSize($this->getBatchSize())
                         ->setCurrentPage(1)
                         ->setSortOrders();

        $search_criteria->setSortOrders($sortOrders);

        $this->queue = $this->pageRepository->getList($search_criteria);
    }

    private function writeln($message, $options = null)
    {
        if ($this->output) {
            $this->output->writeln($message, $options);
        }
    }

    /**
     * Should we send a purge request? There is no point sending it if we don't have varnish
     *
     * @return bool
     */
    private function shouldPurge()
    {
        // we only need to send a purge request if varnish is enabled
        if ($this->scopeConfig->getValue(
            'system/full_page_cache/caching_application',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == 2) {
            return false; // disabled for now as need to either purge by tag or update vcl @todo add config
            return true;
        }
        return false;
    }

    /**
     * Is the full page cache enabled
     *
     * @return bool
     */
    private function cacheEnabled()
    {
        foreach ($this->cacheManager->getStatus() as $cache => $status) {
            if ($cache == 'full_page') {
                return $status;
            }
        }
        return false;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return $this
     */
    public function setOutput(\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getWhenComplete()
    {
        if (null === $this->whenComplete) {
            $this->setWhenComplete(self::WHEN_COMPLETE_SLEEP);
        }
        return $this->whenComplete;
    }

    /**
     * @param $whenComplete
     * @return $this
     * @throws \Exception
     */
    public function setWhenComplete($whenComplete)
    {
        if (!in_array($whenComplete, [self::WHEN_COMPLETE_SLEEP, self::WHEN_COMPLETE_STOP])) {
            throw new \Exception('Invalid Action');
        }

        $this->whenComplete = $whenComplete;
        return $this;
    }

    /**
     * @return int
     */
    public function getSleepBetweenBatch()
    {
        if (null === $this->sleepBetweenBatch) {
            $this->setSleepBetweenBatch($this->configHelper->getSleepBetweenBatch());
        }

        return $this->sleepBetweenBatch;
    }

    /**
     * @param $sleepBetweenBatch
     * @return $this
     */
    public function setSleepBetweenBatch($sleepBetweenBatch)
    {
        $this->sleepBetweenBatch = $sleepBetweenBatch;
        return $this;
    }

    /**
     * @return int
     */
    public function getSleepWhenEmpty()
    {
        if (null === $this->sleepWhenEmpty) {
            $this->setSleepWhenEmpty($this->configHelper->getSleepWhenEmpty());
        }

        return $this->sleepWhenEmpty;
    }

    /**
     * @param $sleepWhenEmpty
     * @return $this
     */
    public function setSleepWhenEmpty($sleepWhenEmpty)
    {
        $this->sleepWhenEmpty = $sleepWhenEmpty;
        return $this;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        if (null === $this->batchSize) {
            $this->setBatchSize($this->configHelper->getBatchSize());
        }

        echo $this->batchSize;
        die();
        return $this->batchSize;
    }

    /**
     * @param $batchSize
     * @return $this
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxRunTime()
    {
        return $this->maxRunTime;
    }

    /**
     * @param $maxRunTime
     * @return $this
     */
    public function setMaxRunTime($maxRunTime)
    {
        $this->maxRunTime = $maxRunTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getCrawlThreshold()
    {
        if (null === $this->crawlThreshold) {
            $this->setCrawlThreshold($this->configHelper->getCrawlThreshold());
        }

        return $this->crawlThreshold;
    }

    /**
     * @param $crawlThreshold
     * @return $this
     */
    public function setCrawlThreshold($crawlThreshold)
    {
        $this->crawlThreshold = $crawlThreshold;
        return $this;
    }

}
