<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class AliYunCDNClientTest extends TestCase
{
    public function testBasicFeature()
    {
        $client = new eddy\AliYunCDNClient('fake-key', 'fake-secret');
        $httpClient = Mockery::mock(Client::class);
        $conent = '{"PageNumber":1,"TotalCount":1,"PageSize":20,"RequestId":"9E5CBC07-8E7A-403C-B0E6-88EC4473AAA9","Tasks":{"CDNTask":[{"CreationTime":"2018-08-16T10:11:16Z","ObjectPath":"http://m.pc6.com/public/js/htm_l5.min.js","Status":"Refreshing","ObjectType":"file","Process":"0%","TaskId":"2942405655"}]}}';
        $httpClient->shouldReceive('get')->with(
            $client->getUrl() . '/?' . $client->buildQuery('DescribeRefreshTasks', []),
            ['timeout' => $client->timeout, 'verify' => false]
        )
            ->andReturn(new \GuzzleHttp\Psr7\Response(200, [], $conent))->once();
        $client->setHttpClient($httpClient);

        $response = $client->DescribeRefreshTasks([]);
        $this->assertSame($conent, (string) $response->getBody());
    }
}