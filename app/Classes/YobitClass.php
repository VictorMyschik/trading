<?php

namespace App\Classes\Trade;

use App\Classes\TradeBaseClass;
use App\Classes\TradingInterface;
use App\Helpers\MrCacheHelper;
use Carbon\Carbon;

class YobitClass extends TradeBaseClass implements TradingInterface
{
  private array $precision = [];

  public function getPairsByName(string $name, string $delimiter = '/'): array
  {
    $pairs = array();

    foreach($this->getPairsSettings() as $key => $item) {
      $tmp = explode('_', (string)mb_convert_case($key, MB_CASE_UPPER, "UTF-8"));

      if($name !== $tmp[1]) {
        continue;
      }

      $pairs[$key] = implode($delimiter, $tmp);
    }

    ksort($pairs);

    return $pairs;
  }

  public function getAllPairs(string $delimiter = '/'): array
  {
    $pairs = array();

    foreach($this->getPairsSettings() as $key => $item) {
      $tmp = explode('_', (string)mb_convert_case($key, MB_CASE_UPPER, "UTF-8"));


      $pairs[$key] = implode($delimiter, $tmp);
    }

    ksort($pairs);

    return $pairs;
  }

  public function getPricePrecision(string $delimiter = '/'): array
  {
    if($this->precision) {
      return $this->precision;
    }
    else {
      $this->precision = MrCacheHelper::GetCachedData('yobit_price_precision', function() use ($delimiter) {
        $pairs = array();
        foreach($this->getPairsSettings() as $key => $item) {
          $pairs[$key] = $item['decimal_places'];
        }
        ksort($pairs);

        return $pairs;
      });
    }

    return $this->precision;
  }

  public function getPairsSettings(): array
  {
    return MrCacheHelper::GetCachedData(self::class . '_pairs', function() {
      $url = "https://yobit.net/api/3/info";

      return $this->api($url)['pairs'];
    });
  }

  protected function apiQuery($api_name, array $req = array()): mixed
  {
    $req['method'] = $api_name;
    $req['nonce'] = time() + rand(1, 5);

    $post_data = http_build_query($req, '', '&');
    $sign = hash_hmac("sha512", $post_data, env('YOBIT_SECRET'));
    $headers = array(
      'Sign: ' . $sign,
      'Key: ' . env('YOBIT_KEY'),
    );

    $ch = null;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; SMART_API PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
    curl_setopt($ch, CURLOPT_URL, 'https://yobit.net/tapi/');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    $res = curl_exec($ch);
    if($res === false) {
      $e = curl_error($ch);
      curl_close($ch);

      return null;
    }
    curl_close($ch);

    return json_decode($res, true);
  }

  public function addOrder(float $price, string $pairName, string $kind, float $quantity): mixed
  {
    $tmp_num = explode('.', $quantity);
    $tmp1 = $tmp_num[1] ?? 0;
    $precisionDiff = pow(10, -strlen($tmp1));
    $finalQuantity = $quantity - $precisionDiff;
    $parameters = array(
      "pair"   => $pairName,  //"BTC_USD",
      "amount" => $finalQuantity,
      "rate"   => $price,
      "type"   => $kind
    );

    return $this->apiQuery('Trade', $parameters);
  }

  public function cancelOrder(int $order_id)
  {
    $this->apiQuery('CancelOrder', ["order_id" => $order_id]);
  }

  public function getBalance(): array
  {
    $response = $this->apiQuery('getInfo', []);

    $balance_out_array = array();
    if(isset($response['return'])) {
      foreach($response['return']['funds'] as $crypto_name => $balance) {
        $balance_out_array[$crypto_name] = (float)$balance;
      }
    }

    return $balance_out_array;
  }

  public function getOrderBook(int $limit = 100): array
  {
    $result_book = array();
    $urlBook = "https://yobit.net/api/3/depth/$this->pair?limit=50";
    $book = $this->parseOrderBook($this->api($urlBook));

    $urlHistory = "https://yobit.net/api/3/trades/$this->pair?limit=50";
    $history = $this->parseHistory($this->api($urlHistory));

    foreach($book as $key => $item) {
      $result_book[] = array_merge($item, $history[$key] ?? array());
    }

    return $result_book;
  }


  private function api(string $url)
  {
    return json_decode(file_get_contents($url), true);
  }

  public function parseHistory(array $data): array
  {
    $out = array();

    foreach($data[$this->pair] as $row) {
      $amount = round($row['amount'], 5);
      $price = round($row['price'], 5);

      $item = array();
      $item['KindTraded'] = $row['type'] == 'buy' ? self::KIND_BUY : self::KIND_SELL;
      // ????????????????????
      $item['QuantityTraded'] = $amount;
      $item['PriceTraded'] = $price;
      // ??????????
      $item['SumTraded'] = $amount * $price;
      $item['TimeTraded'] = Carbon::createFromTimestamp($row['timestamp'])->toDateTime()->format('H:i:s');

      $out[] = $item;
    }

    return $out;
  }

  public function getOpenOrder(string $pair): array
  {
    $out = array();
    $list = $this->apiQuery('ActiveOrders', ['pair' => $pair]);

    if($list['success'] === 1) {
      if(isset($list['return'])) {
        foreach($list['return'] as $key => $row) {
          $item = $row;
          $item['order_id'] = $key;

          $out[] = $item;
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

    return $this->apiQuery('user_trades', $parameters);
  }

  public function parseOrderBook(array $data): array
  {
    $rows = [];

    // ????????????????????
    foreach($data[$this->pair]['asks'] as $key => $item) {

      $priceSell = round($item[0], 8);
      $quantitySell = round($item[1], 4);
      $sumSell = $priceSell * $quantitySell;

      $row = array();
      $row['PriceSell'] = $priceSell;
      $row['QuantitySell'] = $quantitySell;
      $row['SumSell'] = $sumSell;

      $priceBuy = round($data[$this->pair]['bids'][$key][0], 8);
      $quantityBuy = round($data[$this->pair]['bids'][$key][1], 4);
      $sumBuy = $priceBuy * $quantityBuy;

      $row['PriceBuy'] = $priceBuy;
      $row['QuantityBuy'] = $quantityBuy;
      $row['SumBuy'] = $sumBuy;

      $rows[] = $row;
    }

    return $rows;
  }
}