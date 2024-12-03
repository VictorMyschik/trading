<?php

declare(strict_types=1);

namespace App\Classes\DTO\Components;

final readonly class OrderBookComponent
{
    public function __construct(
        public float $priceSell,
        public float $quantitySell,
        public float $sumSell,
        public float $priceBuy,
        public float $quantityBuy,
        public float $sumBuy,
    ) {}
}
