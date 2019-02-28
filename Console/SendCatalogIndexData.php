<?php

namespace Bitbull\Tooso\Console;

use Bitbull\Tooso\Api\Service\Indexer\DataSenderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendCatalogIndexData extends Command
{
    /**
     * @var DataSenderInterface
     */
    protected $dataSender;

    /**
     * @param DataSenderInterface $dataSender
     */
    public function __construct(
        DataSenderInterface $dataSender
    )
    {
        $this->dataSender = $dataSender;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('tooso:index:catalog-send')
            ->setDescription('Send catalog index data to Tooso');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Sending catalog data to Tooso...</info>');
        $this->dataSender->sendCatalog();
        $output->writeln('<info>Done!</info>');
    }
}
