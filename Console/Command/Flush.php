<?php

namespace EightWire\Primer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Flush extends Command
{

    private $pageRepository;


    /**
     * Flush constructor.
     *
     * @param \EightWire\Primer\Api\PageRepositoryInterface $pageRepository
     */
    public function __construct(
        \EightWire\Primer\Api\PageRepositoryInterface $pageRepository

    ) {
        $this->pageRepository = $pageRepository;

        parent::__construct();
    }

    /**
     * Console config
     */
    protected function configure()
    {
        $this->setName('primer:flush')
            ->setDescription('Invalidate all urls so that crawler will reprime everything');
    }

    /**
     * Main execute function - invalidate all urls in primer table to force recrawl
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pageRepository->flush();
        $output->writeln('<comment>crawler pages flushed - all urls will be crawled</comment>');
    }
}
