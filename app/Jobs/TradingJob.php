<?php

namespace App\Jobs;

use App\Classes\TradeBaseClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TradingJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected array $input;

  public function __construct(array $input)
  {
    $this->input = $input;
    $this->connection = 'database';
    $this->queue = $input['queueName'];
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $className = $this->input['stock'] . 'Class';
    $class = "App\\Classes\\" . $className;

    $object = new $class($this->input);
    $object->trade();

    TradeBaseClass::tradingByStock($this->input);
  }
}
