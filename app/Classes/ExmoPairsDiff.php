<?php

namespace App\Classes;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;

class ExmoPairsDiff
{
    const KIND_SELL = 'sell';
    const KIND_BUY = 'buy';

    public function getData(): array
    {
        $pairList = $this->getPairsByName('USDT');

        $result = [];
        foreach ($pairList as $pair => $pairData) {
            try {
                $orderBook = $this->getOrderBook($pair);

                $result[$pair] = [
                    'middle' => $this->setSkipSum($orderBook),
                    'period' => $this->calculatePeriod($orderBook),
                    'diff'   => $this->calculateDifferentPrice($orderBook)
                ];
            } catch (\Exception $e) {

            }
        }

        return $result;
    }

    protected function getPairsSettings(): array
    {
        return Cache::remember('exmo_pair_settings', 100, function () {
            return $this->apiQuery('pair_settings');
        });
    }

    public function getPairsByName(string $name, string $delimiter = '/'): array
    {
        $pairs = array();

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


    public static function parseOrderBook(array $data, string $pair): array
    {
        $rows = [];

        // Количество
        foreach ($data[$pair]['ask'] as $key => $item) {
            try {
                $row = array();
                $row['PriceSell'] = round($item[0], 8);
                $row['QuantitySell'] = round($item[1], 4);
                $row['SumSell'] = round($item[2], 4);

                $row['PriceBuy'] = round($data[$pair]['bid'][$key][0], 8);
                $row['QuantityBuy'] = round($data[$pair]['bid'][$key][1], 4);
                $row['SumBuy'] = round($data[$pair]['bid'][$key][2], 4);

                $rows[] = $row;
            } catch (\Exception $e) {

            }
        }

        return $rows;
    }

    public function getOrderBook(string $pair, int $limit = 100): array
    {
        $result_book = array();
        $param = array('pair' => $pair, 'limit' => $limit);
        $book = self::parseOrderBook($this->apiQuery('order_book', $param), $pair);

        $param = array('pair' => $pair);
        $history = self::parseHistory(self::apiQuery('trades', $param), $pair);

        foreach ($book as $key => $item) {
            $result_book[] = array_merge($item, $history[$key] ?? array());
        }
        return $result_book;
    }

    public static function parseHistory(array $data, string $pair): array
    {
        $out = array();

        foreach ($data[$pair] as $row) {
            $item = array();
            $item['KindTraded'] = $row['type'] == 'buy' ? self::KIND_BUY : self::KIND_SELL;
            // Количество
            $item['QuantityTraded'] = round($row['quantity'], 4);
            $item['PriceTraded'] = round($row['price'], 5);
            // Сумма
            $item['SumTraded'] = round($row['amount'], 5);
            $item['TimeTraded'] = Carbon::createFromTimestamp($row['date'])->toDateTime()->format('H:i:s');
            $item['timestamp'] = $row['date'];

            $out[] = $item;
        }

        return $out;
    }

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

    private function calculateDifferentPrice(array $orderBook): float
    {
        return round($orderBook[0]['PriceSell'] * 100 / (float)$orderBook[0]['PriceBuy'] - 100, 2);
    }

    private function calculatePeriod(array $orderBook): float
    {
        $list = [];
        for ($i = 0; $i < count($orderBook) - 1; $i++) {
            $list[] = $orderBook[$i]['timestamp'] - $orderBook[$i + 1]['timestamp'];
        }

        return array_sum($list) / count($orderBook);
    }

    private function setSkipSum(array $orderBook): float
    {
        $sum = 0;
        foreach ($orderBook as $item) {
            if (!isset($item['SumTraded']))
                return 0.0;

            $sum += $item['SumTraded'];
        }

        return $sum / count($orderBook);
    }

}
