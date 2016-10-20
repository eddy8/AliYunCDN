阿里云CDN SDK

## 说明
阿里云CDN API参考手册：https://help.aliyun.com/document_detail/27155.html

http 请求使用 Guzzle 三方库（v5.3），具体文档可参考：https://github.com/guzzle/guzzle

## 安装
```
composer require eddy\aliyuncdn
```

## 使用方法
```
// 实例化客户端
$client = new eddy\AliYunCDNClient('key', 'secret');
// 发送请求
$response = $client->RefreshObjectCaches(['ObjectPath' => 'url']);
// http 请求返回内容
echo $response->getBody();
// http 响应状态码
echo $response->getStatusCode();
```