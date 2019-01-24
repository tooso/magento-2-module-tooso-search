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
        // TODO: Implement getFilterParam() method.
    }

    /**
     * @inheritdoc
     */
    public function getOrderParam()
    {
        // TODO: Implement getOrderParam() method.
    }

    /**
     * @inheritdoc
     */
    public function isSortHandled()
    {
        return $this->getFilterParam() !== null;
    }

    /**
     * @inheritdoc
     */
    public function areFiltersHandled()
    {
        return false;
    }
}