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
        Schema::create('storefront_product_options', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('storefront_product_id');
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('type');
            $table->integer('min')->default(0);
            $table->integer('max')->default(0);
            $table->boolean('required')->default(false);
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
        Schema::dropIfExists('storefront_product_options');
    }
};
