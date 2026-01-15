<?php

declare(strict_types=1);

namespace Tangwei\Http;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
            ],
            'listeners' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
            ],
        ];
    }
}
