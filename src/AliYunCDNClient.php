<?php
namespace eddy;

use GuzzleHttp\Client;

class AliYunCDNClient
{
    public $timeout = 10;

    protected $key;
    protected $secret;

    protected $url = 'https://cdn.aliyuncs.com';

    /**
     * @var \GuzzleHttp\Client Http Client
     */
    protected $httpClient;

    protected $common_params;

    public function __construct($key = '', $secret = '')
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->httpClient = new Client();
        $orginTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $this->common_params = [
            'Format' => 'JSON',
            'Version' => '2014-11-11',
            'SignatureMethod' => 'HMAC-SHA1',
            'TimeStamp' => date('Y-m-d\TH:i:s\Z'),
            'SignatureVersion' => '1.0',
            'SignatureNonce' => uniqid(),
        ];
        date_default_timezone_set($orginTimeZone);
    }

    public function __call($name, $arguments)
    {
        $query = $this->buildQuery($name, $arguments);

        try {
            $response = $this->httpClient->get(
                $this->url . '/?' . $query,
                ['timeout' => $this->timeout, 'verify' => false]
            );
            return $response;
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $response = $e->getResponse();
            return $response;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function buildQuery($name, $arguments)
    {
        if (empty($arguments)) {
            $origin_params = array_merge(['Action' => $name, 'AccessKeyId' => $this->key]);
        } else {
            if (isset($arguments[0]) && is_array($arguments[0])) {
                $origin_params = array_merge(['Action' => $name, 'AccessKeyId' => $this->key], $arguments[0]);
            } else {
                throw new \InvalidArgumentException('the type of argument must be array.');
            }
        }
        $params = array_merge($this->common_params, $origin_params);

        $query = $this->getQuery($params);
        $origin_params['Signature'] = $this->getSign($query);

        return $this->getQuery(array_merge($this->common_params, $origin_params));
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function setCommonParams($common_params)
    {
        $this->common_params = $common_params;
    }

    public function setHttpClient($client)
    {
        $this->httpClient = $client;
    }

    protected function getQuery($params)
    {
        ksort($params);
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    protected function getSign($query, $method = "GET")
    {
        $str = $method . '&' . rawurlencode('/') . '&' . rawurlencode($query);
        return base64_encode(hash_hmac('sha1', $str, $this->secret . '&', true));
    }
}