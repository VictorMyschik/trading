<?php

declare(strict_types=1);

namespace App\Classes;

use App\Classes\DTO\Components\OpenOrderComponent;
use App\Classes\DTO\Components\OrderBookComponent;
use Illuminate\Support\Facades\Cache;

class PayeerClass extends TradeBaseClass
{
    public function getOrderBook(): array
    {
        $limit = 25;
        $pair = $this->pair;

        $rawOrderBook = $this->client->getOrderBook(['pair' => $pair]);

        $rows = [];

        if (!isset($rawOrderBook['pairs'][$pair]['ask'])) {
            return $rows;
        }

        foreach ($rawOrderBook['pairs'][$pair]['asks'] as $key => $item) {
            if ($key >= $limit) {
                break;
            }

            $row = new OrderBookComponent(
                priceSell: round((float)$item['price'], 8),
                quantitySell: round((float)$item['amount'], 8),
                sumSell: round((float)$item['value'], 8),
                priceBuy: round((float)$rawOrderBook['pairs'][$pair]['bids'][$key]['price'], 8),
                quantityBuy: round((float)$rawOrderBook['pairs'][$pair]['bids'][$key]['amount'], 4),
                sumBuy: round((float)$rawOrderBook['pairs'][$pair]['bids'][$key]['value'], 4),
            );
            $rows[] = $row;
        }

        return $rows;
    }

    protected function getBalance(): array
    {
        $response = $this->client->getBalance();

        $balanceOut = array();

        if (isset($response['balances'])) {
            foreach ($response['balances'] as $cryptoName => $balance) {
                $balanceOut[$cryptoName] = (float)$balance['available'];
            }
        }

        return $balanceOut;
    }

    protected function getPairsSettings(): array
    {
        return Cache::rememberForever(self::class . '_PairsSettings', function () {
            return $this->client->getPairsSettings();
        });
    }

    protected function getOpenOrder(): array
    {
        $list = $this->client->getOpenOrders();

        if (empty($list['items'])) {
            return [];
        } else {
            $out = array();
            foreach ($list as $row) {
                if (is_array($row)) {
                    foreach ($row as $item) {
                        $out[] = new OpenOrderComponent(
                            orderId: (int)$item['id'],
                            pair: $item['pair'],
                            type: $item['action'],
                            amount: (float)$item['amount'],
                            price: (float)$item['price'],
                            value: (float)$item['value'],
                        );
                    }
                }
            }
        }

        return $out;
    }

    protected function addOrder(float $price, string $pairName, string $kind, float $quantity): void
    {
        $tmpNum = (explode('.', (string)$quantity));
        $precisionDiff = pow(10, -strlen($tmpNum[1]));
        $finalQuantity = $quantity - $precisionDiff;

        $this->client->addOrder([
            'pair'   => $pairName,
            'type'   => 'limit',
            'action' => $kind,
            'amount' => $finalQuantity,
            'price'  => $price,
        ]);
    }

    protected function getPricePrecision(): array
    {
        if (!count($this->precision)) {
            $this->precision = Cache::rememberForever(self::class . '_price_precision', function () {
                $pairs = [];
                foreach ($this->getPairsSettings()['pairs'] as $key => $item) {
                    $pairs[$key] = $item['price_prec'];
                }
                ksort($pairs);

                return $pairs;
            });
        }

        return $this->precision;
    }

    public function getHistory(): array
    {
        return [];
    }

}
