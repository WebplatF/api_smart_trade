<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TradeEntry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->string('date', 10)->index();
            $table->string('pair', 10);
            $table->decimal('lot_size', 10, 2);
            $table->enum('direction', ['BUY', 'SELL'])->index();
            $table->decimal('entry_price', 10, 2);
            $table->decimal('stop_loss', 10, 2);
            $table->decimal('take_profit', 10, 2);
            $table->decimal('exit_price', 10, 2);
            $table->decimal('points_captured', 10, 2);
            $table->enum('win_loss', ['WIN', 'LOSS'])->index();
            $table->string('risk_reward', 10);
            $table->string('reason', 20)->nullable();
            $table->decimal('profit', 10, 2)->nullable();
            $table->decimal('loss', 10, 2)->nullable();
            $table->string('remark', 20);
            $table->boolean('is_delete')->default('false');
            $table->timestamps();
            $table->foreign('wallet_id')->references('id')->on('Wallet')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TradeEntry');
    }
};
