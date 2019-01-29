<?php

namespace Bitbull\Tooso\Console;

use Bitbull\Tooso\Api\Service\SearchInterface\Proxy as SearchInterfaceProxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\State;

class GenerateDummyData extends Command
{
    const ARGUMENT_SEARCH = 'search';
    const OPTION_WEBSITE = 'website';
    const OPTION_NAME = 'name';
    const OPTION_LIMIT = 'limit';

    /**
     * @var SearchInterfaceProxy
     *
     * Use a proxy class to delay instantiation of session which in turn needs area code which is set only after.
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
     * @var State
     */
    protected $state;


    /**
     * GenerateDummyData constructor.
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param State $state
     * @param SearchInterfaceProxy $search
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        State $state,
        SearchInterfaceProxy $search
    ) {
        $this->search = $search;
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
            ->setDefinition(
                [
                    new InputArgument(
                        self::ARGUMENT_SEARCH,
                        InputArgument::REQUIRED,
                        'Search Query'
                    ),
                    new InputOption(
                        self::OPTION_WEBSITE,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Product Website',
                        1
                    ),
                    new InputOption(
                        self::OPTION_NAME,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Product Name'
                    ),
                    new InputOption(
                        self::OPTION_LIMIT,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Limit',
                        12
                    )
                ]
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $queryText = $input->getArgument(self::ARGUMENT_SEARCH);
        $output->writeln("<info>Searching for '$queryText'...</info>");

        /** @var \Tooso\SDK\Search\Result $result */
        $result = $this->search->execute($name);

        if ($result->isResultEmpty()) {
            $output->writeln("<error>No results for '$queryText'</error>");
            return;
        }

        $productWebsite = $input->getOption(self::OPTION_WEBSITE);

        $productName = $input->getOption(self::OPTION_NAME);
        if ($productName === null) {
            $productName = ucfirst($queryText);
        }

        $limit = $input->getOption(self::OPTION_LIMIT);
        $results = $result->getResults();
        $results = array_splice($results, 0, $limit);

        foreach ($results as $key => $sku) {
            try {
                $this->productRepository->get($sku);
                $output->writeln("SKU '$sku' already exist, skipping");
                continue;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {

                $output->write("SKU '$sku' not exist, creating product..");
                $product = $this->productFactory->create();
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                $product->setVisibility(4);
                $product->setSku($sku);
                $product->setName($productName . ' ' . ($key + 1));
                $product->setDescription("Product autogenerated searching for '$queryText' on Tooso");
                $product->setPrice(rand(10, 100));
                $product->setAttributeSetId(4);
                $product->setWebsiteIds([$productWebsite]);
                $product->save();
                $this->productRepository->save($product);

                $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
                $stockItem->setIsInStock(true);
                $stockItem->setQty(100);
                $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
                $output->writeln(' OK!');
            }
        }

        $output->writeln('<info>Done!</info>');
    }
}
