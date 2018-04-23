<?php

namespace EightWire\Primer\Observer;

use Magento\Framework\Event\ObserverInterface;

class FlushCrawler implements ObserverInterface
{
    /**
     * @var \EightWire\Primer\Api\PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * FlushAllCache constructor.
     *
     * @param \EightWire\Primer\Api\PageRepositoryInterface $pageRepository
     */
    public function __construct(
        \EightWire\Primer\Api\PageRepositoryInterface $pageRepository,
        \Psr\Log\LoggerInterface $loggerInterface

    ) {
        $this->pageRepository = $pageRepository;
        $this->logger = $loggerInterface;
    }

    /**
     * Flash crawler page urls
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        die('here');
//        $this->logger->info('flushing primer cache - '.$observer->getEventName());
//        $this->pageRepository->flush();
    }
}
