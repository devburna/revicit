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
            $table->string('key')->unique();
            $table->longText('token')->unique();
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
