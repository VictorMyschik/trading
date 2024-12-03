<?php

declare(strict_types=1);

namespace App\Classes\Client;

use GuzzleHttp\Client;

class PayeerClient
{
    private const string HOST = 'https://payeer.com';

    public function getBalance(): array
    {
        $req = json_encode([
            'ts' => round(microtime(true) * 1000),
        ]);

        $sign = hash_hmac('sha256', 'account' . $req, env('PAYEER_KEY'));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST . '/api/trade/account');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "API-ID: " . env('PAYEER_API_ID'),
            "API-SIGN: " . $sign
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return $data;
    }

    public function getOrderBook(array $request): array
    {
        $client = new Client();
        $response = $client->post(self::HOST . '/api/trade/orders', ['json' => $request]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getOpenOrders(): array
    {
        $req = json_encode(array(
            'ts' => round(microtime(true) * 1000),
        ));

        $sign = hash_hmac('sha256', 'my_orders' . $req, env('PAYEER_KEY'));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST . '/api/trade/my_orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "API-ID: " . env('PAYEER_API_ID'),
            "API-SIGN: " . $sign
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getPairsSettings(): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST . '/api/trade/info');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function addOrder(array $parameters): void
    {
        $req = json_encode(array_merge($parameters, ['ts' => round(microtime(true) * 1000)]));

        $sign = hash_hmac('sha256', 'order_create' . $req, env('PAYEER_KEY'));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST . '/api/trade/order_create');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "API-ID: " . env('PAYEER_API_ID'),
            "API-SIGN: " . $sign
        ));

        $response = curl_exec($ch);
        curl_close($ch);
    }

    public function cancelOrder(int $orderId): void
    {
        $req = json_encode([
            'order_id' => $orderId,
            'ts'       => round(microtime(true) * 1000),
        ]);

        $sign = hash_hmac('sha256', 'order_cancel' . $req, env('PAYEER_KEY'));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST . '/api/trade/order_cancel');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "API-ID: " . env('PAYEER_API_ID'),
            "API-SIGN: " . $sign
        ));

        $response = curl_exec($ch);
        curl_close($ch);
    }
}
