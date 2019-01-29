<?php

namespace Bitbull\Tooso\Console;

use Bitbull\Tooso\Api\Service\SearchInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

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
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param SearchInterface $search
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        SearchInterface $search
    )
    {
        $this->search = $search;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
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
                    InputArgument::REQUIRED,
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
        $output->writeln("<info>Searching for '$name'...</info>");

        /** @var \Tooso\SDK\Search\Result $result */
        $result = null;

        foreach ($result->getResults() as $sku) {
            if ($this->productRepository->get($sku)->getId() === null) {
                $output->writeln("<debug>SKU '$sku' already exist, skipping</debug>");
                continue;
            }
            $output->writeln("<debug>SKU '$sku' not exist, creating product..</debug>");
            $product = $this->productFactory->create();
            $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            $product->setVisibility(4);
            $product->setSku($sku);
            $product->setName('Test');
            $product->setPrice(100);
            $product->setAttributeSetId(4);
            $product->save();
            $this->productRepository->save($product);

            $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
            $stockItem->setIsInStock(true);
            $stockItem->setQty(100);
            $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
            $output->writeln("<debug>Product with SKU '$sku' created!</debug>");
        }

        $output->writeln('<info>Done!</info>');
    }
}
