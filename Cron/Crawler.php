<?php

namespace EightWire\Primer\Cron;

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
        \EightWire\Primer\Model\Crawler $crawler

    ) {
        $this->crawler = $crawler;
    }

    /**
     * execute crawler cron
     */
    public function execute()
    {
        $this->crawler->run();
    }
}
