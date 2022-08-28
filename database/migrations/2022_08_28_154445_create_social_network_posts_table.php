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
        Schema::create('social_network_posts', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('company_id');
            $table->string('identity')->unique();
            $table->string('reference');
            $table->longText('post');
            $table->string('platform');
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
        Schema::dropIfExists('social_network_posts');
    }
};
