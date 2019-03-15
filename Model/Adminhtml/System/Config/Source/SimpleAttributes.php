<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

class SimpleAttributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    const DEFAULT_ATTRIBUTES = [
        'sku', 'name', 'description', 'price', 'short_description'
    ];

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * Attributes constructor.
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setFilterGroups([
            $this->filterGroupBuilder
                ->addFilter(
                    $this->filterBuilder->setField('backend_type')
                        ->setValue([
                            'varchar',
                            'int',
                            'text',
                            'decimal',
                        ])
                        ->setConditionType('in')
                        ->create()
                )
                ->create(),
            $this->filterGroupBuilder
                ->addFilter($this->filterBuilder->setField('frontend_input')
                    ->setValue([
                        'text',
                        'textarea',
                        'multiselect',
                        'select',
                        'boolean',
                        'price'
                    ])
                    ->setConditionType('in')
                    ->create()
                )
                ->create(),
            $this->filterGroupBuilder
                ->addFilter($this->filterBuilder->setField('attribute_code')
                    ->setValue($this->getDefaultAttributes())
                    ->setConditionType('nin')
                    ->create())
                ->create(),
        ]);

        $attributeRepository = $this->attributeRepository->getList($searchCriteria);
        $attributes = [];

        foreach ($attributeRepository->getItems() as $attribute) {
            $frontendLabel = $attribute->getFrontendLabel();
            $attributeCode = $attribute->getAttributeCode();
            $attributes[] = [
                'label' => "$frontendLabel ($attributeCode)",
                'value' => $attributeCode,
            ];
        }

        return $attributes;
    }

    /**
     * Get default attributes
     *
     * @return array
     */
    public function getDefaultAttributes()
    {
        return self::DEFAULT_ATTRIBUTES;
    }
}
