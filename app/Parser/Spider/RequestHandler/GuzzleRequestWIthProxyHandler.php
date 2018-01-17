<?php

namespace App\Parser\Spider\RequestHandler;

use App\Models\TemporarySearchResults;
use App\Parser\Spider\Proxy\FineproxyOrgProxy;
use GuzzleHttp\Client;
use VDB\Spider\RequestHandler\RequestHandlerInterface;
use VDB\Spider\Resource;
use VDB\Spider\Uri\DiscoveredUri;

class GuzzleRequestWIthProxyHandler implements RequestHandlerInterface
{
    /** @var Client */
    private $client;
    private $proxyUrl;

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new Client();
        }
        return $this->client;
    }

    /**
     * @param DiscoveredUri $uri
     * @return \VDB\Spider\Resource
     */
    public function request(DiscoveredUri $uri)
    {
        $response = $this->getClient()->get($uri->toString(), [
            'proxy' => $this->getProxyUrl(),
            'http_errors' => false
        ]);
        if ($response->getStatusCode() == 404) {
            TemporarySearchResults::where(['content->url' => "{$uri->toString()}"])
                ->update([
                    'need_delete' => true,
                    'version' => 0,
                ]);
            return null;
        }
        return new Resource($uri, $response);
    }

    /**
     * @param $uri
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function requestByStringUrl($uri)
    {
        $response = $this->getClient()->get($uri, [
            'proxy' => $this->getProxyUrl()
        ]);
        return $response;
    }

    public function getProxyUrl()
    {
        if (!$this->proxyUrl) {
            $proxy = new FineproxyOrgProxy();
            $this->proxyUrl = $proxy->getProxyUrl();
        }
        return $this->proxyUrl;
    }
}