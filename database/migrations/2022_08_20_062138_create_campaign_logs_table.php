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
        Schema::create('campaign_logs', function (Blueprint $table) {
            $table->id()->from(time());
            $table->unsignedBigInteger('campaign_id');
            $table->string('sender_name');
            $table->string('sender_email')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->timestamp('recipient_phone')->nullable();
            $table->longText('meta');
            $table->string('message');
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
        Schema::dropIfExists('campaign_logs');
    }
};
