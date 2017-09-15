<?php

namespace App\Parser\Spider\RequestHandler;

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
            'proxy' => $this->getProxyUrl()
        ]);
        return new Resource($uri, $response);
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