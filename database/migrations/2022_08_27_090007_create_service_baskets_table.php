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
        Schema::create('service_baskets', function (Blueprint $table) {
            $table->id()->from(time());
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->longText('description');
            $table->string('category');
            $table->string('network');
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('price_capped_at', 15, 2)->default(0);
            $table->string('currency');
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
        Schema::dropIfExists('service_baskets');
    }
};
