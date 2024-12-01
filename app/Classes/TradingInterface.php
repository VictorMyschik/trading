<?php

namespace App\Classes;

interface TradingInterface
{
    public function getOrderBook(): array;

    public function getHistory(): array;
}
