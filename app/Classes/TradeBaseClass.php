<?php

declare(strict_types=1);

namespace App\Classes;

use App\Classes\Client\PayeerClient;
use App\Classes\DTO\Components\OpenOrderComponent;
use App\Jobs\TradingJob;
use App\Models\MrTrading;

abstract class TradeBaseClass implements TradingInterface
{
    public const string KIND_SELL = 'sell';
    public const string KIND_BUY = 'buy';
    protected array $precision = [];

    public const int STRATEGY_BASE = 1;
    public const int STRATEGY_SMART_ANALISE = 2;
    protected mixed $quantityMin;
    protected array $calculatedOpenOrders;
    protected PayeerClient $client;


    public static function getStrategyList(): array
    {
        return [
            self::STRATEGY_BASE          => 'Базовая',
            self::STRATEGY_SMART_ANALISE => 'Аналитика истории'
        ];
    }

    public function __construct(
        public int       $strategy,
        public float|int $skipSum,
        public string    $pair,
        public float     $diff,
        public float|int $quantityMax,
    )
    {
        $this->client = new PayeerClient();
        $this->quantityMin = $this->getPairsSettings()['pairs'][$this->pair]['min_value'];
    }

    public function trade(): void
    {
        $fullOrderBook = $this->getOrderBook();
        if (!count($fullOrderBook)) {
            return;
        }
        $balance = $this->getBalance();

        if (!isset($balance[explode('_', $this->pair)[1]])) {
            return;
        }

        $orderBookDiff = $this->getOrderBookDiff($fullOrderBook);
        $fullOpenOrders = $this->getOpenOrder();

        $this->calculatedOpenOrders = $this->groupOpenOrders($fullOpenOrders);

        switch ($this->strategy) {
            case self::STRATEGY_BASE:
                $this->baseStrategy($orderBookDiff, $fullOpenOrders, $fullOrderBook, $balance);
                break;
            case self::STRATEGY_SMART_ANALISE:
                $this->smartAnaliseStrategy($fullOpenOrders, $fullOrderBook, $balance);
                break;
            default:
                throw new \Exception('Unknown strategy');
        }
    }

    private function getOrderBookDiff(array $fullOrderBook): float
    {
        return round($fullOrderBook[0]->priceSell * 100 / (float)$fullOrderBook[0]->priceBuy - 100, 2);
    }

    /**
     * Group orders by pair names
     */
    private function groupOpenOrders(array $data): array
    {
        $openOrders = [];

        /** @var OpenOrderComponent $item */
        foreach ($data as $item) {
            if (isset($openOrders[$item->pair])) {
                $openOrders[$item->pair] += round($item->amount, 8);
            } else {
                $openOrders[$item->pair] = round($item->amount, 8);
            }
        }

        return $openOrders;
    }

    private function correctHasOrders(array $fullOpenOrder, array $orderBook): bool
    {
        /** @var OpenOrderComponent $openOrder */
        foreach ($fullOpenOrder as $openOrder) {
            // Has open order
            if ($this->pair === $openOrder->pair) {
                // Update order
                if (!$this->isActual($openOrder, $orderBook)) {
                    $this->cancelOrder($openOrder->orderId);

                    return true;
                }
            }
        }

        return false;
    }

    protected function cancelOrder(int $orderId): void
    {
        $this->client->cancelOrder($orderId);
    }

    private function isActual(OpenOrderComponent $openOrder, array $orderBook): bool
    {
        $kind = $openOrder->type;
        $price = $openOrder->price ?? $openOrder['rate'];

        $precision = $this->getPricePrecision()[$openOrder->pair];
        $priceKeyName = ($kind == self::KIND_SELL) ? 'priceSell' : 'priceBuy';
        $sumKeyName = ($kind == self::KIND_SELL) ? 'sumSell' : 'sumBuy';

        $myOpenPrice = round($price, $precision);

        $orderBookItem = $orderBook[0];
        $sum = 0;
        foreach ($orderBook as $item) {
            // exclude self order
            if ($item->$priceKeyName == $price) {
                continue;
            }

            $sum += $item->$sumKeyName;
            if ($sum > $this->skipSum) {
                $orderBookItem = $item;
                break;
            }
        }

        $orderPrice = $orderBookItem->$priceKeyName;

        if ($kind == self::KIND_SELL) {
            $precisionDiff = pow(10, -$precision);
            $orderPrice = $orderPrice - $precisionDiff;
        }

        if ($kind == self::KIND_BUY) {
            $precisionDiff = pow(10, -$precision);
            $orderPrice = $orderPrice + $precisionDiff;
        }

        $orderPrice = round($orderPrice, $precision);

        if ((string)$orderPrice != (string)$myOpenPrice) {
            return false; // need update order
        }

        return true;
    }

    private function tradeByOrder(array $balance, array $fullOpenOrders, array $orderBook, string $pairName): void
    {
        $currencyFirst = explode('_', $pairName)[0];
        $currencySecond = explode('_', $pairName)[1];
        $balanceValue = $balance[$currencyFirst] ?? 0;

        /// Sell MNX
        if ($balanceValue > $this->quantityMin) {
            // Cancel open orders. Disable many orders, one only
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['type'] === self::KIND_SELL && $this->pair === $openOrder['pair']) {
                    $this->cancelOrder($openOrder->order_id);

                    return;
                }
            }

            // Create new order
            $newPrice = $this->getNewPrice($orderBook, self::KIND_SELL, $pairName);
            $this->addOrder($newPrice, $pairName, self::KIND_SELL, $balanceValue);

            return;
        }

        /// Buy MNX
        $balanceValue = $balance[$currencySecond] ?? 0;
        if ($balanceValue > 0.01) {
            $allowMaxTradeSum = min($balanceValue, $this->quantityMax);

            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['type'] === self::KIND_BUY && $this->pair === $openOrder['pair']) {
                    $this->cancelOrder($openOrder['order_id']);

                    return;
                }
            }

            // Create new order
            $newPrice = $this->getNewPrice($orderBook, self::KIND_BUY, $pairName);

            $quantity = $allowMaxTradeSum / $newPrice;

            if ($quantity <= $this->quantityMin) {
                return;
            }

            $this->addOrder($newPrice, $pairName, self::KIND_BUY, $quantity);
        }
    }

    private function getNewPrice(array $orderBook, string $type, string $pairName): float
    {
        $precision = $this->getPricePrecision()[$pairName];
        $precisionDiff = pow(10, -$precision);

        // Get price skipping "small" amount row
        $orderBookItem = $orderBook[0];
        $sum = 0;
        foreach ($orderBook as $item) {
            $sum += ($type == self::KIND_SELL) ? $item->sumSell : $item->sumBuy;
            if ($sum > $this->skipSum) {
                $orderBookItem = $item;
                break;
            }
        }

        if ($type == self::KIND_SELL) {
            $oldPriceSell = (float)$orderBookItem->priceSell;
            $newPrice = $oldPriceSell - $precisionDiff;
        } else { // Buy
            $oldPriceBuy = (float)$orderBookItem->priceBuy;
            $newPrice = $oldPriceBuy + $precisionDiff;
        }

        return round($newPrice, $precision);
    }

    public static function runTrading(): void
    {
        foreach (MrTrading::all() as $item) {
            if (!$item->isActive()) {
                continue;
            }

            $parameter = [
                'strategy'  => $item->getStrategy(),
                'stock'     => $item->getStock()->getName(),
                'diff'      => $item->getDifferent(),
                'maxTrade'  => $item->getMaxTrade(),
                'pair'      => strtoupper($item->getPair()),
                'queueName' => 'default',//strtolower($item->id() . '_queue'),
                'skipSum'   => $item->getSkipSum(),
            ];

            self::tradingByStock($parameter);
        }
    }

    #region Strategics
    private function baseStrategy(float $orderBookDiff, array $fullOpenOrders, array $fullOrderBook, array $balance): void
    {
        // If diff smaller than commission - cancel all orders
        if ($orderBookDiff < $this->diff) {
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['pair'] === $this->pair) {
                    $this->cancelOrder($openOrder->order_id);
                }
            }
        } else {
            $needRestart = $this->correctHasOrders($fullOpenOrders, $fullOrderBook);
            if (!$needRestart) {
                $this->tradeByOrder($balance, $fullOpenOrders, $fullOrderBook, $this->pair);
            }
        }
    }

    private function smartAnaliseStrategy(array $fullOpenOrders, array $fullOrderBook, array $balance): void
    {
        $history = $this->getHistory();

        $currentOrders = reset($fullOrderBook);
        $currentPriceBuy = $currentOrders['PriceBuy'];
        $currentPriceSell = $currentOrders['PriceSell'];


        /// Find Price Buy
        // Actual price to open order for buy
        $priceSuy = array_column($history['sell'], 'PriceTraded');
        $minBuy = min($priceSuy);

        // 2/3 percent of diff
        $diff = ($currentPriceBuy * 100 / $minBuy - 100) / 5 * 1;
        $finalPriceBuy = $currentPriceBuy - ($currentPriceBuy / 100 * $diff);
        $finalPriceBuy = round($finalPriceBuy, $this->getPricePrecision()[$this->pair]);

        /// Find Price Sell
        // Actual price to open order for sale
        $priceBuy = array_column($history['buy'], 'PriceTraded');
        $maxSell = max($priceBuy);

        // 2/3 percent of diff
        $diff = ($maxSell * 100 / $currentPriceSell - 100) / 5 * 1;
        $finalPriceSell = $currentPriceSell / 100 * $diff + $currentPriceSell;
        $finalPriceSell = round($finalPriceSell, $this->getPricePrecision()[$this->pair]);

        $commissionTakerMaker = ($this->getPairsSettings()[$this->pair]['commission_maker_percent']) * 3; // %

        // Cancel Open Orders
        $d = $finalPriceSell * 100 / $finalPriceBuy - 100;
        if ($d < $commissionTakerMaker) {
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['pair'] === $this->pair) {
                    $out['cancelOrder'] = $openOrder['order_id'];
                    $this->cancelOrder($openOrder['order_id']);
                }
            }
        }

        // Trade
        [$currencyFirst, $currencySecond] = explode('_', $this->pair);

        $balanceValue = $balance[$currencyFirst] ?? 0;

        /// Sell MNX
        // Correct opened order
        foreach ($fullOpenOrders as $openOrder) {
            if ($openOrder['type'] === self::KIND_SELL
                && $this->pair === $openOrder['pair']
                && $openOrder['price'] !== $finalPriceSell
            ) {
                $this->cancelOrder($openOrder['order_id']);
                return;
            }
        }

        if ($balanceValue > $this->quantityMin) {
            // Cancel open orders. Disable many orders, one only
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['type'] === self::KIND_SELL && $this->pair === $openOrder['pair']) {
                    $this->cancelOrder($openOrder['order_id']);

                    return;
                }
            }

            // Create new order
            $this->addOrder($finalPriceSell, $this->pair, self::KIND_SELL, $balanceValue);
        }


        /// Buy MNX
        // Correct opened order
        foreach ($fullOpenOrders as $openOrder) {
            if ($openOrder['type'] === self::KIND_BUY
                && $this->pair === $openOrder['pair']
                && $openOrder['price'] !== $finalPriceBuy
            ) {
                $this->cancelOrder($openOrder['order_id']);
                return;
            }
        }

        $balanceValue = $balance[$currencySecond] ?? 0;
        if ($balanceValue > 0.01) {
            $allowMaxTradeSum = min($balanceValue, $this->quantityMax);

            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['type'] === self::KIND_BUY && $this->pair === $openOrder['pair']) {
                    $this->cancelOrder($openOrder['order_id']);

                    return;
                }
            }

            // Create new order
            $quantity = $allowMaxTradeSum / $finalPriceBuy;

            if ($quantity <= $this->quantityMin) {
                return;
            }

            $this->addOrder($finalPriceBuy, $this->pair, self::KIND_BUY, $quantity);
        }

        sleep(2);
    }

    #endregion

    #region Commands
    public static function stopTrading()
    {
        echo exec('supervisorctl reread all') . '<br>';
        echo exec('supervisorctl update') . '<br>';
        echo exec('supervisorctl restart all') . '<br>';
        echo exec('cd /var/www/trading') . '<br>';
        echo exec('php artisan queue:clear') . '<br>';
        echo exec('php artisan queue:clear') . '<br>';
        echo exec('php artisan config:clear') . '<br>';
        echo exec('php artisan cache:clear') . '<br>';
        echo exec('redis-cli -h localhost -p 6379 flushdb') . '<br>';
        echo exec('php artisan horizon:pause') . '<br>';
        echo exec('php artisan horizon:clear') . '<br>';
    }

    public static function tradingByStock(array $parameter)
    {
        TradingJob::dispatch($parameter);
    }
    #endregion
}
