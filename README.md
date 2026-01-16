# HTTP/2 客户端 & 基于 HTTP/2 的 Hyperf RPC 组件

## 📦 安装

```bash
composer require tangwei/http
```

## 🔧 HTTP/2 客户端

### 示例

```php
$domain = 'httpbin.org';
// 这个client是协程安全的，可以复用
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

## 🚀 基于 HTTP/2 的 Hyperf RPC 组件

> **注意**: 在使用此组件前，请先配置好 JSON-RPC，详情请参阅 [Hyperf JSON-RPC 文档](https://hyperf.wiki/3.1/#/zh-cn/json-rpc)

使用 `jsonrpc-http` 协议

### 服务端配置

修改 `config/autoload/dependencies.php` 配置文件，替换默认的传输器实现：

```php
return [
    // ...
    \Hyperf\JsonRpc\JsonRpcHttpTransporter::class => \Tangwei\Http\JsonRpcHttp2Transporter::class,
];
```

### 客户端配置

修改 `config/autoload/services.php` 配置文件，配置客户端参数：

```php
<?php

declare(strict_types=1);

return [
    'consumers' => [
        [
            // ...
            'options' => [
                // HTTP/2 客户端连接池数量
                'client_count' => 4,
            ],
        ],
    ],
];
```

### 启用 HTTP/2 协议

确保在 `config/autoload/server.php` 配置文件中启用 HTTP/2 协议：

```php
return [
    // ...
    'settings' => [
        // ...
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
    ],
];
```

## 📊 性能测试

### 测试命令

```bash
ab -n 200000 -c 500 http://127.0.0.1:19501/rpc/id
```

### 性能对比

| 协议 | 请求处理能力 | 提升倍数 |
|------|-------------|----------|
| HTTP/2 | 7,107.54 req/sec | 2.36x |
| HTTP/1.1 | 3,008.05 req/sec | 1.00x |

> 💡 **结论**: 使用 HTTP/2 协议相比传统 HTTP/1.1 协议，在高并发场景下性能提升显著，达到约 2.36 倍的性能提升！

## ✨ 特性

- 🚀 高性能 HTTP/2 客户端实现
- 🔄 自动连接管理和重连机制
- 📊 多路复用，单连接并发处理
- 🛡️ 完整的 Hyperf RPC 生态兼容性
- ⚡ 显著的性能提升

## 🤝 贡献

欢迎提交 Issue 和 Pull Request 来帮助改进此项目！
