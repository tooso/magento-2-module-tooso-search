<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use Tooso\SDK\Exception;
use Tooso\SDK\Response;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface ClientInterface
{
    /**
     * Do a request on Tooso API
     *
     * @param string $path
     * @param string $httpMethod
     * @param array $params
     * @param int $timeout
     * @return Response
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function doRequest($path, $httpMethod = \Tooso\SDK\Client::HTTP_METHOD_GET, array $params = array(), $timeout = null);
}
