<?php

namespace App\Console\Commands;

use App\Jobs\TradingJob;
use App\Models\MrTrading;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class restart extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'restart';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    echo exec('supervisorctl reread all').'<br>';
    echo exec('supervisorctl update').'<br>';
    echo exec('supervisorctl restart all').'<br>';
    echo exec('cd /var/www/trading').'<br>';
    echo exec('php artisan queue:clear').'<br>';
    echo exec('php artisan queue:clear').'<br>';
    echo exec('php artisan config:clear').'<br>';
    echo exec('php artisan cache:clear').'<br>';
    echo exec('redis-cli -h localhost -p 6379 flushdb').'<br>';
    echo exec('php artisan horizon:clear').'<br>';

    $this->runTrading();

    return 0;
  }

  public function runTrading()
  {
    foreach(MrTrading::all() as $item) {
      if(!$item->isActive()) {
        continue;
      }

      $parameter = [
        'stock'     => $item->getStock()->getName(),
        'diff'      => $item->getDifferent(),
        'maxTrade'  => $item->getMaxTrade(),
        'pair'      => strtoupper($item->getPair()),
        'queueName' => strtolower($item->id() . '_queue'),
        'skipSum'   => $item->getSkipSum(),
      ];

      self::tradingByStock($parameter);
    }
  }

  public static function tradingByStock(array $parameter)
  {
    TradingJob::dispatch($parameter);
  }
}
