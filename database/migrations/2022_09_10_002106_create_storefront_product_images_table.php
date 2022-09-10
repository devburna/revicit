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
        Schema::create('storefront_product_images', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('storefront_product_id');
            $table->string('name')->nullable();
            $table->string('image_url');
            $table->longText('meta');
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
        Schema::dropIfExists('storefront_product_images');
    }
};
