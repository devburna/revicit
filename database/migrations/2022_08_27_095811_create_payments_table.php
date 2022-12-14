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
        Schema::create('payments', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('company_wallet_id');
            $table->string('identity')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('currency');
            $table->string('narration');
            $table->string('type');
            $table->string('status');
            $table->longText('meta');
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
        Schema::dropIfExists('payments');
    }
};
