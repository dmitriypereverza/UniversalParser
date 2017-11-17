<?php

namespace App\Parser\Version;

class VersionManager
{
    const GET_NEW_VERSION_NUMBER = '/api/parser/get_new_version_num';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    private $token;
    private $baseUrl;

    public function __construct(\GuzzleHttp\Client $client, $baseUrl, $token = Null)
    {
        $this->client = $client;
        $this->token = $token;
        $this->baseUrl = $baseUrl;
    }

    public function getNewVersion($elementCount) {
        $response = $this->request($this->baseUrl . self::GET_NEW_VERSION_NUMBER, [
            'element_count' => (int)$elementCount
        ]);
        return (int)$response['version'];
    }

    private function request($url, $params) {
        $response = $this->client->post(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]
        );
        if ($response->getStatusCode() == 200) {
            return \GuzzleHttp\json_decode($response->getBody(), true);
        }
    }
}