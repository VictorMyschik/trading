<?php

declare(strict_types=1);

namespace App\Classes\DTO\Components;

final readonly class OpenOrderComponent
{
    public function __construct(
        public int       $orderId,
        public string    $pair,
        public string    $type,
        public float|int $amount,
        public float|int $price,
        public float|int $value,
    ) {}
}
