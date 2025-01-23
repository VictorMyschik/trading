<?php

namespace App\Jobs;

use App\Classes\Client\YobitClient;
use App\Classes\TradeBaseClass;
use App\Classes\Yobit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TradingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $input)
    {
        $this->connection = 'database';
    }

    public function handle(): void
    {
        $service = new Yobit(
            strategy: $this->input['strategy'],
            skipSum: $this->input['skipSum'],
            pair: $this->input['pair'],
            diff: $this->input['diff'],
            quantityMax: $this->input['quantityMax'],
            quantityMin: $this->input['quantityMin'],
            client: new YobitClient(),
        );

        $service->trade();

        sleep(1);
        TradeBaseClass::tradingByStock($this->input);
    }
}
