<?php

namespace Bitbull\Tooso\Console;

use Bitbull\Tooso\Api\Service\SearchInterfaceFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\State;

class GenerateDummyData extends Command
{
    /**
     * Search argument
     */
    const SEARCH_ARGUMENT = 'search';

    /**
     * @var SearchInterface
     */
    protected $searchFactory;

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
     * @var State
     */
    protected $state;

    /**
     * @param SearchInterfaceFactory $searchFactory
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param State $state
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        State $state,
        SearchInterfaceFactory $searchFactory
    )
    {
        $this->searchFactory = $searchFactory;
        $this->state = $state;
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
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $search = $this->searchFactory->create();

        $name = $input->getArgument(self::SEARCH_ARGUMENT);
        $output->writeln("<info>Searching for '$name'...</info>");

        /** @var \Tooso\SDK\Search\Result $result */
        $result = $search->execute($name);

        foreach ($result->getResults() as $key => $sku) {
            try{
                $this->productRepository->get($sku);
                $output->writeln("<debug>SKU '$sku' already exist, skipping</debug>");
                continue;
            }catch (\Magento\Framework\Exception\NoSuchEntityException $exception){

                $output->writeln("<debug>SKU '$sku' not exist, creating product..</debug>");
                $product = $this->productFactory->create();
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                $product->setVisibility(4);
                $product->setSku($sku);
                $product->setName($name . ' ' . $key);
                $product->setPrice(100);
                $product->setAttributeSetId(4);
                $product->setWebsiteId(1);
                $product->save();
                $this->productRepository->save($product);

                $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
                $stockItem->setIsInStock(true);
                $stockItem->setQty(100);
                $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
                $output->writeln("<debug>Product with SKU '$sku' created!</debug>");
            }
        }

        $output->writeln('<info>Done!</info>');
    }
}
