<?php

declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: 狂奔的螞蟻 <www.firstphp.com>
 * Date: 2019/9/17
 * Time: 下午16:28
 */

namespace Firstphp\Tower\Bridge;

use Hyperf\Guzzle\ClientFactory;


class Http
{

    /**
     * @var string
     */
    protected $baseUri = 'https://tower.im/';


    /**
     * @var array
     */
    protected $options;


    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $clientFactory;


    /**
     * Http constructor.
     * @param array $config
     * @param ClientFactory $clientFactory
     */
    public function __construct(array $config = [], ClientFactory $clientFactory)
    {
        $baseUri = isset($config['url']) && $config['url'] ? $config['url'] : $this->baseUrl;
        $tokenInfo = isset($config['tokenInfo']) && $config['tokenInfo'] ? $config['tokenInfo'] : '';
        $this->baseUrl = $baseUri;
        $this->clientFactory = $clientFactory;
        $this->options = [
            'base_uri' => $baseUri,
            'timeout' => 2.0,
            'verify' => false,
            'headers' => [
                'Authorization' => "Bearer {$tokenInfo['access_token']}",
                'Content-Type' => "application/json;charset=utf-8",
            ]
        ];
    }


    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $client = $this->clientFactory->create($this->options);
        $response = json_decode($client->$name($arguments[0], $arguments[1])->getBody()->getContents(), true);
        if (isset($response['errcode']) && $response['errcode'] != 0) {
            return $response;
        }
        return $response;
    }


}