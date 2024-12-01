<?php

namespace App\Classes;

use App\Helpers\MrCacheHelper;
use Carbon\Carbon;
use Mockery\Exception;

class ExmoClass extends TradeBaseClass implements TradingInterface
{
    /**
     * Order Book with trades history
     */
    public function getOrderBook(int $limit = 25): array
    {
        $param = array('pair' => $this->pair, 'limit' => $limit);

        return $this->parseOrderBook(self::apiQuery('order_book', $param));
    }

    public function getHistory(): array
    {
        return $this->parseHistory(self::apiQuery('trades', ['pair' => $this->pair]));
    }

    private function parseOrderBook(array $data): array
    {
        $rows = [];

        if (!isset($data[$this->pair]['ask'])) {
            return $rows;
        }

        foreach ($data[$this->pair]['ask'] as $key => $item) {
            $row = array();
            $row['PriceSell'] = round($item[0], 8);
            $row['QuantitySell'] = round($item[1], 4);
            $row['SumSell'] = round($item[2], 4);

            $row['PriceBuy'] = round($data[$this->pair]['bid'][$key][0], 8);
            $row['QuantityBuy'] = round($data[$this->pair]['bid'][$key][1], 4);
            $row['SumBuy'] = round($data[$this->pair]['bid'][$key][2], 4);

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * API Exmo
     * Downloaded from https://github.com/exmo-dev/exmo_api_lib/blob/master/php/exmo.php
     */
    private function apiQuery($apiName, array $req = []): mixed
    {
        $mt = explode(' ', microtime());
        // API settings
        $url = "https://api.exmo.com/v1.1/$apiName";
        $req['nonce'] = $mt[1] . substr($mt[0], 2, 6);
        // generate the POST data string
        $postData = http_build_query($req);
        $sign = hash_hmac('sha512', $postData, env('EXMO_SECRET'));
        // generate the extra headers
        $headers = array(
            'Sign: ' . $sign,
            'Key: ' . env('EXMO_KEY'),
        );

        static $ch = null;

        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // run the query
        $res = curl_exec($ch);
        if ($res === false) {
            throw new Exception('Could not get reply: ' . curl_error($ch));
        }
        $dec = @json_decode($res, true);
        if ($dec === null) {
            throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
        }

        return $dec;
    }

    protected function parseHistory(array $data): array
    {
        $out = array();
        if (!isset($data[$this->pair])) {
            return $out;
        }

        foreach ($data[$this->pair] as $row) {
            $item = array();

            $item['QuantityTraded'] = round($row['quantity'], 8);
            $item['PriceTraded'] = round($row['price'], 8);

            $item['SumTraded'] = round($row['amount'], 8);
            $item['TimeTraded'] = Carbon::createFromTimestamp($row['date'])->toDateTime()->format('H:i:s');
            $item['timestamp'] = $row['date'];

            $out[$row['type'] == 'buy' ? self::KIND_BUY : self::KIND_SELL][] = $item;
        }

        return $out;
    }

    protected function getPricePrecision(): array
    {
        if (!count($this->precision)) {
            $this->precision = MrCacheHelper::GetCachedData(self::class . '_price_precision', function () {
                $pairs = [];
                foreach ($this->getPairsSettings() as $key => $item) {
                    $pairs[$key] = $item['price_precision'];
                }
                ksort($pairs);

                return $pairs;
            });
        }

        return $this->precision;
    }

    protected function getPairsSettings(): array
    {
        return MrCacheHelper::GetCachedData(self::class . '_PairsSettings', function () {
            return $this->apiQuery('pair_settings', array());
        });
    }

    protected function getBalance(): array
    {
        $response = $this->apiQuery('user_info', array());

        $balanceOut = array();

        if (isset($response['balances'])) {
            foreach ($response['balances'] as $cryptoName => $balance) {
                $balanceOut[$cryptoName] = (float)$balance;
            }
        }

        return $balanceOut;
    }

    protected function addOrder(float $price, string $pairName, string $kind, float $quantity): mixed
    {
        $tmpNum = (explode('.', $quantity));
        $precisionDiff = pow(10, -strlen($tmpNum[1]));
        $finalQuantity = $quantity - $precisionDiff;

        $parameters = array(
            "pair"     => $pairName,  //"BTC_USD",
            "quantity" => $finalQuantity,
            "price"    => $price,
            "type"     => $kind
        );

        return $this->apiQuery('order_create', $parameters);
    }

    protected function cancelOrder(int $orderId)
    {
        $this->apiQuery('order_cancel', ["order_id" => $orderId]);
    }

    protected function getOpenOrder(): array
    {
        $list = $this->apiQuery('user_open_orders', array());

        if (isset($list['result'])) {
            return [];
        } else {
            $out = array();
            foreach ($list as $row) {
                if (is_array($row)) {
                    foreach ($row as $item) {
                        $out[] = $item;
                    }
                }
            }
        }

        return $out;
    }
}