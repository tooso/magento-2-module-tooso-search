<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    CONST CUSTOM_ATTRIBUTES = [
        'is_in_stock' => 'Is in stock',
        'variants' => 'Variants',
        'categories' => 'Categories',
        'image' => 'Image',
        'gallery' => 'Image Gallery',
        'qty' => 'Stock Quantity',
    ];

    /**
     * @var array
     */
    const DEFAULT_ATTRIBUTES = [
        'sku', 'visibility', 'status', 'image', 'description', 'is_in_stock', 'categories', 'gallery', 'name', 'price', 'variants'
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
                    ->create())
                ->create(),
            $this->filterGroupBuilder
                ->addFilter($this->filterBuilder->setField('attribute_code')
                    ->setValue($this->getDefaultAttributes())
                    ->setConditionType('nin')
                    ->create())
                ->create()
        ]);

        $attributeRepository = $this->attributeRepository->getList($searchCriteria);

        $customAttributes = self::CUSTOM_ATTRIBUTES;
        $matchedKeys = array_filter(array_keys($customAttributes), function($attribute){
            return !in_array($attribute, self::DEFAULT_ATTRIBUTES, true);
        });
        $customAttributes = array_intersect_key($customAttributes, array_flip($matchedKeys));

        $attributes = array_map(function($customAttributeLabel, $customAttributeValue) {
            return [
                'label' => "$customAttributeLabel ($customAttributeValue)",
                'value' => $customAttributeValue,
            ];
        }, $customAttributes,  array_keys($customAttributes));

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
     * Get custom attributes
     *
     * @return array
     */
    public function getCustomAttributes()
    {
        return array_keys(self::CUSTOM_ATTRIBUTES);
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
