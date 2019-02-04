<?php

namespace EightWire\Primer\Cron;

use EightWire\Primer\Model\Crawler as CrawlerModel;

class Crawler
{

    /**
     * @var \EightWire\Primer\Model\Crawler
     */
    private $crawler;

    /**
     * Crawler constructor.
     * @param \EightWire\Primer\Model\Crawler $crawler
     */
    public function __construct(
        CrawlerModel $crawler

    ) {
        $this->crawler = $crawler;
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

    protected function enableOnCron()
    {
        //@todo get from config
        return false;
    }

    protected function getOnComplete()
    {
        //@todo get from config
        return CrawlerModel::WHEN_COMPLETE_STOP;
    }
}
