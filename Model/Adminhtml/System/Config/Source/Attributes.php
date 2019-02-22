<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
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
     * @var FilterGroupBuilder
     */
    protected $customAttributes = [
        'is_in_stock' => 'Is in stock',
        'variants' => 'Variants',
        'categories' => 'Categories',
        'image' => 'Image',
        'gallery' => 'Image Gallery',
        'qty' => 'Stock Quantity',
    ];

    /**
     * @param SystemStore $systemStore
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
                ->create()
        ]);
        
        $attributeRepository = $this->attributeRepository->getList($searchCriteria);
        $attributes = array_map(function($customAttributeLabel, $customAttributeValue) {
            return [
                'label' => $customAttributeLabel,
                'value' => $customAttributeValue,
            ];
        }, $this->customAttributes,  array_keys($this->customAttributes));
    
        foreach ($attributeRepository->getItems() as $attribute) {
            $attributes[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode(),
            ];
        }
        
        return $attributes;
    }
}
