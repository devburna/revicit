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
        Schema::create('ayrshare_profiles', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('company_id')->unique();
            $table->string('identity')->unique();
            $table->string('reference')->unique();
            $table->boolean('facebook')->default(false);
            $table->boolean('fbg')->default(false);
            $table->boolean('gmb')->default(false);
            $table->boolean('instagram')->default(false);
            $table->boolean('linkedin')->default(false);
            $table->boolean('pinterest')->default(false);
            $table->boolean('reddit')->default(false);
            $table->boolean('telegram')->default(false);
            $table->boolean('tiktok')->default(false);
            $table->boolean('twitter')->default(false);
            $table->boolean('youtube')->default(false);
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
        Schema::dropIfExists('ayrshare_profiles');
    }
};
