<?php

namespace Bitbull\Tooso\Console;

use \Bitbull\Tooso\Api\Service\SearchInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDummyData extends Command
{
    /**
     * Search argument
     */
    const SEARCH_ARGUMENT = 'search';

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @param SearchInterface $search
     */
    public function __construct(SearchInterface $search)
    {
        $this->search = $search;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('tooso:dummy-data')
            ->setDescription('Generate dummy data based on Tooso search response')
            ->setDefinition([
                new InputArgument(
                    self::SEARCH_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Search Query'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument(self::SEARCH_ARGUMENT);
        $output->writeln("<info>Searching for $name</info>");
    }
}
