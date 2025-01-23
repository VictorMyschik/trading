<?php

namespace App\Classes;

use App\Classes\DTO\Components\OpenOrderComponent;
use App\Classes\DTO\Components\OrderBookComponent;
use App\Helpers\MrCacheHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Yobit extends TradeBaseClass implements TradingInterface
{
    protected array $precision = [];

    public function getPairsByName(string $name, string $delimiter = '/'): array
    {
        foreach ($this->getPairsSettings() as $key => $item) {
            $tmp = explode('_', (string)mb_convert_case($key, MB_CASE_UPPER, "UTF-8"));

            if ($name !== $tmp[1]) {
                continue;
            }

            $pairs[$key] = implode($delimiter, $tmp);
        }

        ksort($pairs);

        return $pairs;
    }

    public function getPairsSettings(): array
    {
        return Cache::rememberForever('yobit_pairs_settings', function () {
            $list = $this->client->getPairSettings();
            $newList = [];
            foreach ($list['pairs'] as $key => $item) {
                $newList['pairs'][strtoupper($key)] = [
                    'min_value'      => $item['min_amount'],
                    'decimal_places' => $item['decimal_places'],
                ];
            }

            return $newList;
        });
    }

    private function api(string $url)
    {
        return json_decode(file_get_contents($url), true);
    }

    public function getAllPairs(string $delimiter = '/'): array
    {
        $pairs = array();

        foreach ($this->getPairsSettings() as $key => $item) {
            $tmp = explode('_', (string)mb_convert_case($key, MB_CASE_UPPER, "UTF-8"));


            $pairs[$key] = implode($delimiter, $tmp);
        }

        ksort($pairs);

        return $pairs;
    }

    public function getPricePrecision(string $delimiter = '/'): array
    {
        if ($this->precision) {
            return $this->precision;
        } else {
            $this->precision = MrCacheHelper::GetCachedData('yobit_price_precision', function () {
                $pairs = array();
                foreach ($this->getPairsSettings()['pairs'] as $key => $item) {
                    $pairs[$key] = $item['decimal_places'];
                }
                ksort($pairs);

                return $pairs;
            });
        }

        return $this->precision;
    }

    public function addOrder(float $price, string $pairName, string $kind, float $quantity): mixed
    {
        $tmpNum = explode('.', $quantity);
        $tmp1 = $tmpNum[1] ?? 0;
        $precisionDiff = pow(10, -strlen($tmp1));
        $finalQuantity = $quantity - $precisionDiff;
        // Отнимем комиссию 0,2%
        $finalQuantity = $finalQuantity - ($finalQuantity * 0.002);
        // Округляем до 8 знаков в меньшую сторону
        $finalQuantity = round($finalQuantity, 8, PHP_ROUND_HALF_DOWN);

        $parameters = array(
            "pair"   => $pairName,
            "amount" => $finalQuantity,
            "rate"   => $price,
            "type"   => $kind
        );

        return $this->client->apiQuery('Trade', $parameters);
    }

    public function cancelOrder(int $orderId): void
    {
        $this->client->apiQuery('CancelOrder', ["order_id" => $orderId]);
    }

    public function getBalance(): array
    {
        $response = $this->client->apiQuery('getInfo', []);

        $balanceOutArray = array();
        if (isset($response['return'])) {
            foreach ($response['return']['funds'] as $crypto_name => $balance) {
                $balanceOutArray[strtoupper($crypto_name)] = (float)$balance;
            }
        }

        return $balanceOutArray;
    }

    public function getOrderBook(int $limit = 100): array
    {
        $list = $this->client->getOrderBook($this->pair);
        return $this->parseOrderBook($list);
    }

    public function getHistory(): array
    {
        $urlHistory = "https://yobit.net/api/3/trades/$this->pair?limit=50";
        return $this->parseHistory($this->api($urlHistory));

    }

    public function parseOrderBook(array $rawOrderBook): array
    {
        $rows = [];
        $pair = strtolower($this->pair);
        // Количество
        foreach ($rawOrderBook[$pair]['asks'] as $key => $item) {

            $priceSell = round($item[0], 8);
            $quantitySell = round($item[1], 4);
            $sumSell = $priceSell * $quantitySell;

            if (!isset($rawOrderBook[$pair]['bids'][$key])) {
                break;
            }
            $priceBuy = round($rawOrderBook[$pair]['bids'][$key][0], 8);
            $quantityBuy = round($rawOrderBook[$pair]['bids'][$key][1], 4);
            $sumBuy = $priceBuy * $quantityBuy;

            $row = new OrderBookComponent(
                priceSell: round($priceSell, 8),
                quantitySell: round($quantitySell, 8),
                sumSell: round($sumSell, 8),
                priceBuy: round($priceBuy, 8),
                quantityBuy: round($quantityBuy, 4),
                sumBuy: round($sumBuy, 4),
            );

            $rows[] = $row;
        }

        return $rows;
    }

    public function parseHistory(array $data): array
    {
        $out = array();

        foreach ($data[$this->pair] as $row) {
            $amount = round($row['amount'], 5);
            $price = round($row['price'], 5);

            $item = array();
            $item['KindTraded'] = $row['type'] == 'buy' ? self::KIND_BUY : self::KIND_SELL;
            // Количество
            $item['QuantityTraded'] = $amount;
            $item['PriceTraded'] = $price;
            // Сумма
            $item['SumTraded'] = $amount * $price;
            $item['TimeTraded'] = Carbon::createFromTimestamp($row['timestamp'])->toDateTime()->format('H:i:s');

            $out[] = $item;
        }

        return $out;
    }

    public function getOpenOrder(string $pair): array
    {
        $out = array();
        $list = $this->client->apiQuery('ActiveOrders', ['pair' => $pair]);

        if ($list['success'] === 1) {
            if (isset($list['return'])) {
                foreach ($list['return'] as $key => $row) {
                    $out[] = new OpenOrderComponent(
                        orderId: (int)$key,
                        pair: strtoupper($row['pair']),
                        type: $row['type'],
                        amount: (float)$row['amount'],
                        price: (float)$row['rate'],
                        value: 0, // not used
                    );
                }
            }
        }

        return $out;
    }

    public function GetMyCompletedTradeList(string $pairs): array
    {
        $parameters = array(
            "pair" => $pairs, "limit" => 15, "offset" => 0
        );

        return $this->client->apiQuery('user_trades', $parameters);
    }
}
