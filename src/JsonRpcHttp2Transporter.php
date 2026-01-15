<?php

declare(strict_types=1);

namespace Tangwei\Http;

use Hyperf\Collection\Arr;
use Hyperf\Coroutine\Locker;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\Rpc\Contract\TransporterInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Swoole\Http2\Request;

class JsonRpcHttp2Transporter implements TransporterInterface
{
    /**
     * @var Http2Client[]
     */
    protected array $clients = [];

    protected array $config = [
        'client_count' => 4,
    ];

    private ?LoadBalancerInterface $loadBalancer = null;

    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);
    }

    public function send(string $data)
    {
        $client = $this->get();
        $request = new Request();
        $request->method = 'POST';
        $request->headers = [
            'content-type' => 'application/json',
            // 'accept-encoding' => 'gzip'
        ];
        $request->data = $data;
        $response = $client->request($request);
        if ($response !== false) {
            return $response->data;
        }

        return '';
    }

    public function recv()
    {
        throw new RuntimeException(__CLASS__ . ' does not support recv method.');
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $this->loadBalancer = $loadBalancer;
        return $this;
    }

    protected function get(): Http2Client
    {
        if ($this->clients === []) {
            $key = spl_object_hash($this);
            if (Locker::lock($key)) {
                try {
                    $this->refresh();
                } finally {
                    Locker::unlock($key);
                }
            }
        }

        return Arr::random($this->clients);
    }

    protected function refresh(): void
    {
        $nodes = $this->getNodes();
        $nodeCount = count($nodes);
        $count = (int) ($this->config['client_count'] ?? 4);
        for ($i = 0; $i < $count; ++$i) {
            $node = $nodes[$i % $nodeCount];
            if (! isset($this->clients[$i])) {
                $Client = new Http2Client($node->host, $node->port);
                $Client->connect();
                $this->clients[$i] = $Client;
            }
        }
    }

    protected function getNodes(): array
    {
        $nodes = $this->getLoadBalancer()->getNodes();
        if (empty($nodes)) {
            throw new RuntimeException('No Available Nodes');
        }

        return $nodes;
    }
}
