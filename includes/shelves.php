<?php

function shop_lines(): array
{
    return [
        'cards' => [
            'name' => 'Cards',
            'line' => 'Greeting and occasion cards',
            'codes' => ['0100050', '0100051', '0100052', '0100053', '0100054', '0100055'],
        ],
        'hampers' => [
            'name' => 'Hampers',
            'line' => 'Arranged gift hampers',
            'codes' => ['0800007', '0800008', '0800009', '0800010', '0800011', '0800012'],
        ],
    ];
}

function shop_line(string $key): ?array
{
    $lines = shop_lines();
    return $lines[$key] ?? null;
}
