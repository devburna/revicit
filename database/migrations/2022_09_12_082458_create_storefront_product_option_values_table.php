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
        Schema::create('storefront_product_option_values', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('storefront_product_option_id');
            $table->string('label');
            $table->string('image_url')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('default')->default(false);
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
        Schema::dropIfExists('storefront_product_option_values');
    }
};
