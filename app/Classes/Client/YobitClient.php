<?php

declare(strict_types=1);

namespace App\Classes\Client;

class YobitClient implements StockClientInterface
{
    public function getPairSettings(): array
    {
        return json_decode(file_get_contents("https://yobit.net/api/3/info"), true);
    }

    public function getOrderBook(string $pair): array
    {
        $pair = strtolower($pair);
        $urlBook = "https://yobit.net/api/3/depth/$pair?limit=50";
        return json_decode(file_get_contents($urlBook), true);
    }

    public function apiQuery($apiName, array $req = array()): mixed
    {
        sleep(1);

        $req['method'] = $apiName;
        $req['nonce'] = time() + rand(1, 5);

        $postData = http_build_query($req, '', '&');
        $sign = hash_hmac("sha512", $postData, env('YOBIT_SECRET'));
        $headers = array(
            'Sign: ' . $sign,
            'Key: ' . env('YOBIT_KEY'),
        );

        $ch = null;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; SMART_API PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
        curl_setopt($ch, CURLOPT_URL, 'https://yobit.net/tapi/');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        $res = curl_exec($ch);
        if ($res === false) {
            curl_error($ch);
            curl_close($ch);

            return null;
        }
        curl_close($ch);

        return json_decode($res, true);
    }
}
