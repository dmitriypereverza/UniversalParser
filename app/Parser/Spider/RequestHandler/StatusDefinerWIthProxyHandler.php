<?php

namespace App\Parser\Spider\RequestHandler;

use App\Models\Links;
use App\Parser\Spider\Proxy\FineproxyOrgProxy;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use VDB\Spider\RequestHandler\RequestHandlerInterface;
use VDB\Spider\Resource;
use VDB\Spider\Uri\DiscoveredUri;

class StatusDefinerWIthProxyHandler implements RequestHandlerInterface
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
//        $response = $this->getClient()->get($uri->toString(), [
//            'proxy' => $this->getProxyUrl()
//        ]);
        $response = $this->getClient()->get($uri->toString());

        $this->saveInDb($uri, $response);
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

    /**
     * @param DiscoveredUri $uri
     * @param ResponseInterface $response
     */
    protected function saveInDb(DiscoveredUri $uri, $response)
    {
        $link = Links::getOrCreateLinkByUrl($uri);
        $link->server_response_code = $response->getStatusCode();
        $link->save();
    }
}