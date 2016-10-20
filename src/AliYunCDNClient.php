<?php
namespace eddy;

class AliYunCDNClient
{
    public $timeout = 10;

    protected $key;
    protected $secret;

    protected $url = 'https://cdn.aliyuncs.com';

    protected $httpClient;

    protected $common_params;

    function __construct($key = '', $secret = '')
    {
        date_default_timezone_set('UTC');

        $this->key = $key;
        $this->secret = $secret;
        $this->httpClient = new \GuzzleHttp\Client();
        $this->common_params = [
            'Format' => 'JSON',
            'Version' => '2014-11-11',
            'SignatureMethod' => 'HMAC-SHA1',
            'TimeStamp' => date('Y-m-d\TH:i:s\Z'),
            'SignatureVersion' => '1.0',
            'SignatureNonce' => uniqid(),
        ];
    }

    public function __call($name, $arguments)
    {
        if (!is_array($arguments[0])) {
            throw new \InvalidArgumentException('the type of argument must be array.');
        }

        $origin_params = array_merge(['Action' => $name, 'AccessKeyId' => $this->key], $arguments[0]);
        $params = array_merge($this->common_params, $origin_params);

        $query = $this->getQuery($params);
        $origin_params['Signature'] = $this->getSign($query);

        $query = $this->getQuery(array_merge($this->common_params, $origin_params));

        try {
            $response = $this->httpClient->get($this->url . '/?' . $query, ['timeout' => $this->timeout, 'verify' => false]);
            return $response;
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $response = $e->getResponse();
            return $response;
        }
    }

    public function setUrl($url)
    {
        $this->url = $url;
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

    protected function getQuery($params, $encode = false)
    {
        if ($encode === true) {
            $params = array_map('rawurlencode', $params);
        }
        ksort($params);
        return http_build_query($params);
    }

    protected function getSign($query, $method = "GET")
    {
        $str = $method . '&' . rawurlencode('/') . '&' . rawurlencode($query);
        return base64_encode(hash_hmac('sha1', $str, $this->secret . '&', true));
    }
}