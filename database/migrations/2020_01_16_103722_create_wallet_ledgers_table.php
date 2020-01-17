<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_ledgers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->nullableMorphs('transaction');
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('running_raw_balance')->default(0);
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_ledgers');
    }
}
