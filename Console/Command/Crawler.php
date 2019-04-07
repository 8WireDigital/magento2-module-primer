<?php

namespace EightWire\Primer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class Crawler extends Command
{

    private $crawler;

    private $input;

    private $output;

    private static $header = <<<HEADER
   __  ___                   __         _____         __         ___      _              
  /  |/  /__ ____ ____ ___  / /____    / ___/__ _____/ /  ___   / _ \____(_)_ _  ___ ____
 / /|_/ / _ `/ _ `/ -_) _ \/ __/ _ \  / /__/ _ `/ __/ _ \/ -_) / ___/ __/ /  ' \/ -_) __/
/_/  /_/\_,_/\_, /\__/_//_/\__/\___/  \___/\_,_/\__/_//_/\__/ /_/  /_/ /_/_/_/_/\__/_/   
            /___/
            
HEADER;

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
            ->addOption('dump-config', 'd', InputOption::VALUE_OPTIONAL, 'dump run config before running crawler', 0)

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
        $this->output = $output;
        $this->input = $input;
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


        $this->showHeader();

        $this->crawler->run();
    }


    protected function showHeader()
    {
        $this->output->writeln(self::$header);

        if ($this->input->hasParameterOption('--dump-config') || $this->input->hasParameterOption('-d')) {
            $this->output->writeln('Crawl Threshold: '.$this->crawler->getCrawlThreshold());
            $this->output->writeln('Batch Size: '.$this->crawler->getBatchSize());
            $this->output->writeln('Sleep Between Batch: '.$this->crawler->getSleepBetweenBatch());
            $this->output->writeln('Sleep when Empty: '.$this->crawler->getSleepWhenEmpty());
            $this->output->writeln('Max Run Time: '.$this->crawler->getMaxRunTime());

            $this->output->writeln('');

        }
    }
}
