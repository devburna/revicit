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
        Schema::create('storefronts', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('company_id')->unique();
            $table->string('name')->unique();
            $table->string('tagline')->nullable();
            $table->string('domain')->unique();
            $table->longText('description');
            $table->string('logo_url')->nullable();
            $table->string('currency');
            $table->string('welcome_message')->nullable();
            $table->string('success_message')->nullable();
            $table->boolean('delivery_note')->default(false);
            $table->string('redirect_after_payment_url')->nullable();
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
        Schema::dropIfExists('storefronts');
    }
};
