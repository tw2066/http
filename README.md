# http2 客户端 & 基于Http2的hyperf RPC组件

## 安装

```
composer require tangwei/http
```

## http2 客户端
示例
```php
$domain = 'httpbin.org';
$client = new \Tangwei\Http\Http2Client($domain, 443, true);
$client->set([
    'timeout' => -1,
    'ssl_host_name' => $domain,
]);
$client->connect();
for ($i = 1; $i < 30; ++$i) {
    go(function () use ($client, $i) {
        $req = new Request();
        $req->method = 'POST';
        $req->path = '/post';
        $req->headers = [
            'host' => '127.0.0.1',
            'user-agent' => 'Chrome/49.0.2587.3',
            'accept' => 'text/html,application/xhtml+xml,application/xml',
            'accept-encoding' => 'gzip',
        ];
        $req->data = (string) $i;
        $data = $client->request($req);
        $result = json_decode($data->data, true);
    });
}
```
## 基于Http2的hyperf RPC组件

优先配置好服务rpc配置 https://hyperf.wiki/3.1/#/zh-cn/json-rpc

protocol 协议，使用 jsonrpc-http

修改 `config/autoload/dependencies.php` 配置文件。

```php
return [
     // ...
    \Hyperf\JsonRpc\JsonRpcHttpTransporter::class => \Tangwei\Http\JsonRpcHttp2Transporter::class,

];
```

## 客户端配置

修改 `config/autoload/services.php` 配置文件

```php
<?php

declare(strict_types=1);

return [
    'consumers' => [
        [
            // ...
            'options' => [

                // 客户端数量
                'client_count' => 4,
            ],
        ],
    ],
];
```

## hyperf启动http2协议
修改 `config/autoload/services.php` 配置文件

```php
return [
     // ...
    'settings' => [
        // ...
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
    ]

];
```

## 测试

```sh
ab -n 200000 -c 500 http://127.0.0.1:19501/rpc/id
```
测试结果

| 版本             | Requests per second                              |
|----------------|--------------------------------------------------|
| 使用http2        | 7107.54 [#/sec] (mean)                           |
| 默认http         | 3008.05 [#/sec] (mean)                           |

