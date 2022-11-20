<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMrTradingTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('mr_trading', function(Blueprint $table) {
      $table->increments('id');
      $table->integer('StockID')->unsigned();
      $table->float('Different')->default(1);
      $table->float('MaxTrade')->default(50);
      $table->string('Pair');
      $table->float('SkipSum')->default(10);
      $table->string('Description')->nullable();
      $table->boolean('IsActive')->default(0);
      $table->timestamp('WriteDate')->useCurrent()->useCurrentOnUpdate();

      $table->foreign('StockID')->references('id')->on('mr_stock')->onDelete('cascade')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('mr_trading');
  }
}
