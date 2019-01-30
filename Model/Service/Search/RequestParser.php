<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Search;

use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Bitbull\Tooso\Api\Service\Search\RequestParserInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\App\Request\Http as RequestHttp;

class RequestParser implements RequestParserInterface
{
    const PARAM_QUERY = 'q';
    const PARAM_PARENT_SEARCH_ID = 'parentSearchId';
    const PARAM_TYPO_CORRECTION = 'typoCorrection';
    const PARAM_ORDER_TYPE = 'product_list_order';
    const PARAM_ORDER_DIRECTION = 'product_list_dir';

    const ORDER_PARAM_SEPARATOR = '-';
    const ORDER_PARAM_DEFAULT = 'relevance';
    const ORDER_PARAM_DIRECTION_DEFAULT = 'desc';
    const FILTER_PARAM_SEPARATOR = ':';
    const FILTER_PARAM_PREFIX = 'magento_';

    const FILTER_PARAM_PRICE = 'rangePriceFilter';

    /**
     * @var SearchConfigInterface
     */
    protected $searchConfig;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var RequestHttp
     */
    protected $request;

    /**
     * Search constructor.
     *
     * @param SearchConfigInterface $searchConfig
     * @param UrlFactory $urlFactory
     * @param RequestHttp $request
     */
    public function __construct(
        SearchConfigInterface $searchConfig,
        UrlFactory $urlFactory,
        RequestHttp $request
    )
    {
        $this->searchConfig = $searchConfig;
        $this->urlFactory = $urlFactory;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function buildSearchUrl($queryParam, $parentSearchId)
    {
        $url = $this->urlFactory->create();
        $url->setQueryParam(self::PARAM_QUERY, $queryParam);
        $url->setQueryParam(self::PARAM_PARENT_SEARCH_ID, $parentSearchId);
        return $url->getUrl('catalogsearch/result');
    }

    /**
     * @inheritdoc
     */
    public function getParentSearchId()
    {
        return $this->request->getParam(self::PARAM_PARENT_SEARCH_ID);
    }

    /**
     * @inheritdoc
     */
    public function getQueryText()
    {
        return $this->request->getParam(self::PARAM_QUERY);
    }


    /**
     * @inheritdoc
     */
    public function isTypoCorrectedSearch()
    {
        return $this->request->getParam(self::PARAM_PARENT_SEARCH_ID, 'true') === 'true';
    }

    /**
     * @inheritdoc
     */
    public function getFilterParam()
    {
        $excludeParams = $this->searchConfig->getParamFilterExclusion();
        $requestParams = $this->request->getParams();
        $requestParamsKeys = array_filter(array_keys($requestParams),function ($param) use ($excludeParams){
            return !in_array($param, $excludeParams, true);
        });

        $filterValue = '';
        $priceParam = $this->searchConfig->getFilterPriceParam(); // limit multiple access in foreach loop

        foreach ($requestParamsKeys as $requestParamKey) {
            $filterParamValue = $requestParams[$requestParamKey];
            if ($filterValue !== '') {
                $filterValue .= self::FILTER_PARAM_SEPARATOR;
            }
            switch ($requestParamKey){
                case $priceParam:
                    $priceParts = explode('-', $filterParamValue);
                    $priceParts[0] = $priceParts[0] !== '' ? number_format((float) $priceParts[0], 1) : '0.0';
                    $priceParts[1] = $priceParts[1] !== '' ? number_format((float) $priceParts[1], 1) : '10000.0';
                    $filterValue .= self::FILTER_PARAM_PRICE . self::FILTER_PARAM_SEPARATOR . $priceParts[0] . self::FILTER_PARAM_SEPARATOR . self::FILTER_PARAM_PRICE . self::FILTER_PARAM_SEPARATOR . $priceParts[1];
                    break;
                default:
                    $filterValue .= self::FILTER_PARAM_PREFIX . $requestParamKey . self::FILTER_PARAM_SEPARATOR . $filterParamValue;
            }
        }
        return $filterValue === '' ? null : $filterValue;
    }

    /**
     * @inheritdoc
     */
    public function getOrderParam()
    {
        $orderType = $this->request->getParam(self::PARAM_ORDER_TYPE);
        if ($orderType === null) {
            $orderType = self::ORDER_PARAM_DEFAULT;
        }
        $orderValue = $orderType;
        $orderDirection = $this->request->getParam(self::PARAM_ORDER_DIRECTION);
        if ($orderDirection !== null) {
            $orderValue .= self::ORDER_PARAM_SEPARATOR . $orderDirection;
        }else if($orderType !== 'relevance'){
            $orderValue .= self::ORDER_PARAM_SEPARATOR . self::ORDER_PARAM_DIRECTION_DEFAULT;
        }
        if (!in_array($orderValue, $this->searchConfig->getSupportedOrderTypes(), true)) {
            return null;
        }
        return $orderValue;
    }

    /**
     * @inheritdoc
     */
    public function isSortHandled()
    {
        return $this->getOrderParam() !== null;
    }

    /**
     * @inheritdoc
     */
    public function areFiltersHandled()
    {
        return false;
        //NOTE: currently filters are not supported by Tooso
        //return $this->getFilterParam() !== null;
    }
}