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
        Schema::create('company_wallets', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('company_id')->unique();
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('previous_balance', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_wallets');
    }
};
