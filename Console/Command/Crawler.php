<?php

namespace EightWire\Primer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

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
            ->addOption('batch-size', null, InputOption::VALUE_OPTIONAL, 'Max number of pages to crawl per batch')
            ->addOption('max-run-time', null, InputOption::VALUE_OPTIONAL, 'Max time in seconds for the crawler to run before exiting')
            ->addOption('sleep-between-batch', null, InputOption::VALUE_OPTIONAL, 'Time in seconds to wait between batches')
            ->addOption('sleep-when-empty', null, InputOption::VALUE_OPTIONAL, 'Time in seconds to wait before trying again when no pages were crawled')
            ->addOption('crawl-threshold', null, InputOption::VALUE_OPTIONAL, 'Minimum priority for logged page to reach before being crawled')
            ->addOption('when-complete', null, InputOption::VALUE_OPTIONAL, 'What to do when all pages have been logged (sleep or stop)')
            ->setDescription('Initiate primer crawler');
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


        $batchSize = $input->getOption('batch-size');
        if ($batchSize) {
            $this->crawler->setBatchSize($batchSize);
        }

        $maxRunTime = $input->getOption('max-run-time');
        if ($maxRunTime) {
            $this->crawler->setMaxRunTime($maxRunTime);
        }

        $sleepBetweenBatch = $input->getOption('sleep-between-batch');
        if ($sleepBetweenBatch) {
            $this->crawler->setSleepBetweenBatch($sleepBetweenBatch);
        }

        $sleepWhenEmpty = $input->getOption('sleep-when-empty');
        if ($sleepWhenEmpty) {
            $this->crawler->setSleepWhenEmpty($sleepWhenEmpty);
        }

        $crawlThreshold = $input->getOption('crawl-threshold');
        if ($crawlThreshold) {
            $this->crawler->setCrawlThreshold($crawlThreshold);
        }

        $whenComplete= $input->getOption('when-complete');
        if ($whenComplete) {
            $this->crawler->setWhenComplete($whenComplete);
        }

        $this->crawler->run();
    }
}
