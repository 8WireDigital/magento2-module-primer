<?php

namespace EightWire\Primer\Cron;

use EightWire\Primer\Api\CrawlerInterface;

class Crawler
{

    /**
     * @var \EightWire\Primer\Model\Crawler
     */
    private $crawler;

    /**
     * Crawler constructor.
     * @param CrawlerInterface $crawler
     * @param \EightWire\Primer\Helper\Config $configHelper
     */
    public function __construct(
        CrawlerInterface $crawler,
        \EightWire\Primer\Helper\Config $configHelper
    ) {
        $this->crawler = $crawler;
        $this->configHelper = $configHelper;
    }

    /**
     * execute crawler cron
     */
    public function execute()
    {
        if ($this->enableOnCron()) {
            $this->crawler->setWhenComplete($this->getOnComplete())->run();
        }
    }

    /**
     * get config for whether the crawler should run from cron triggers
     * @return mixed
     */
    protected function enableOnCron()
    {
        return $this->configHelper->getCronEnabled();
    }

    /**
     * get config for what cron should do when all pages are crawled
     * @return mixed
     */
    protected function getOnComplete()
    {
        return $this->configHelper->getCronOnComplete();
    }
}
