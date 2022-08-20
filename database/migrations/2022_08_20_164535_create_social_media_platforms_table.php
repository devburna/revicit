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
        Schema::create('social_media_platforms', function (Blueprint $table) {
            $table->id()->from(time());
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('image')->default(false);
            $table->boolean('video')->default(false);
            $table->boolean('reels')->default(false);
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
        Schema::dropIfExists('social_media_platforms');
    }
};
