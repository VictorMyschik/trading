<?php

namespace App\Console\Commands;

use App\Classes\TradeBaseClass;
use Illuminate\Console\Command;

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
    TradeBaseClass::stopTrading();

    TradeBaseClass::runTrading();

    return 0;
  }
}
