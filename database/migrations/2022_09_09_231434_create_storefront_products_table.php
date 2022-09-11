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
        Schema::create('storefront_products', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('storefront_id');
            $table->string('name');
            $table->string('slug');
            $table->longText('description');
            $table->string('tags')->nullable();
            $table->decimal('regular_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->string('quantity');
            $table->unsignedBigInteger('stock_keeping_unit')->default(0);
            $table->unsignedBigInteger('stock_quantity')->default(0);
            $table->string('item_unit')->nullable();
            $table->string('type');
            $table->boolean('low_stock_alert')->default(false);
            $table->unsignedBigInteger('notifiable_stock_quantity')->default(0);
            $table->string('status');
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
        Schema::dropIfExists('storefront_products');
    }
};
