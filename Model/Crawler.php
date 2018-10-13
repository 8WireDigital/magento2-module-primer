<?php
namespace EightWire\Primer\Model;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class Crawler
{

    private $storeManager;

    private $objectManager;

    private $scopeConfig;

    private $queue;

    private $pageRepository;

    private $whenEmpty;
    private $sleepBetweenBatch;
    private $sleepWhenEmpty;
    private $batchSize;

    private $output;

    private $client;


    const WHEN_COMPLETE_SLEEP = 'sleep';
    const WHEN_COMPLETE_STOP = 'stop';


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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;

        /** @var @todo get these from config */
        $this->sleepBetweenBatch = 5;
        $this->sleepWhenEmpty = 10;
        $this->batchSize = 10;

        $this->whenComplete = SELF::WHEN_COMPLETE_SLEEP;

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
        // @todo don't run if full page cache is disabled as would just put load on side

        if (!$this->shouldPrime()) {
            return;
        }
        while (true) {
            $this->getNextBatch();

            if (count($this->queue) < 1) {
                if ($this->whenComplete == self::WHEN_COMPLETE_SLEEP) {
                    $this->writeln('<info>No pages in queue - waiting '.$this->sleepWhenEmpty.' seconds</info>');
                    sleep($this->sleepWhenEmpty); // @codingStandardsIgnoreLine
                } else {
                    $this->writeln('<info>No pages in queue - exiting</info>');
                    return;
                }

            } else {
                $this->writeln('<info>Crawling '.count($this->queue).' pages</info>');
            }

            if ($this->shouldPurge()) {
                $this->purge();
            }
            $this->prime();

            sleep($this->sleepBetweenBatch); // @codingStandardsIgnoreLine
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

            $sendtime = microtime(true);

            $request = new Request('GET', $url);

            $promises[]  = $this->client->sendAsync($request)->then(

                function (Response $response) use ($page, $sendtime, $request) {

                    $responsetime = microtime(true);

                    $this->writeln(
                        '<info>GET   '.$page->getPath() .'</info> <comment>'.$response->getStatusCode().', '.number_format (( $responsetime - $sendtime ), 2).'s</comment>'
                    );
                    $page->setStatus(1);
                    $this->pageRepository->save($page);
                }
            )->otherwise(function(\Exception $e)  use ($page, $sendtime, $request)  {
                $this->writeln(
                    '<error>'.$e->getMessage().'</error>'
                );
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



        $sortOrder = $this->objectManager->create('Magento\Framework\Api\SortOrder');
        $sortOrders = [
            $sortOrder->setField('priority')->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC)
        ];

        /** @var \Magento\Framework\Api\SearchCriteriaInterface $search_criteria */
        $search_criteria = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaInterface');
        $search_criteria->setFilterGroups([$statusFilterGroup])->setPageSize($this->batchSize)->setCurrentPage(1)->setSortOrders();

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
     * Should we send a cache priming request? There is no point sending it if we don't have a full page cache
     *
     * @return bool
     */
    private function shouldPrime()
    {
//        // we only need to send a purge request if varnish is enabled
//        if ($this->scopeConfig->getValue(
//                'system/full_page_cache/caching_application',
//                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
//            ) == 2) {
//            return false; // disabled for now as need to either purge by tag or update vcl @todo add config
//            return true;
//        }
        return true;
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
     * @param $action
     * @return $this
     * @throws \Exception
     */
    public function setWhenComplete($action)
    {
        if (!in_array($action, [self::WHEN_COMPLETE_SLEEP, self::WHEN_COMPLETE_STOP])) {
            throw new \Exception('invalid action');
        }
        $this->whenComplete = $action;
        return $this;
    }
}
