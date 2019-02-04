<?php

namespace EightWire\Primer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Crawler extends Command
{

    private $crawler;

    /**
     * Crawler constructor.
     *
     * @param \EightWire\Primer\Model\Crawler $crawler
     */
    public function __construct(
        \EightWire\Primer\Api\CrawlerInterface $crawler

    ) {
        $this->crawler = $crawler;

        parent::__construct();
    }

    /**
     * Console config
     */
    protected function configure()
    {
        $this->setName('primer:crawler:run')
            ->setDescription('Remove invalid products based on a given search strategy');
    }

    /**
     * Main execute function - trigger crawler
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->crawler->setOutput($output);

        //@todo console params to set crawler config. e.g
//        $this->crawler->setWhenComplete(\EightWire\Primer\Model\Crawler::WHEN_COMPLETE_STOP);
//        $this->crawler->setSleepBetweenBatch(10);
//        $this->crawler->setSleepWhenEmpty(20);
//        $this->crawler->setBatchSize(5);
//        $this->crawler->setMaxRunTime(20);
//        $this->crawler->setCrawlThreshold(3);
        $this->crawler->run();
    }
}
