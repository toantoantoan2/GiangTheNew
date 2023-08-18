<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class chienBao extends Migration
{
    public function up()
    {
        Schema::connection('dbuser')->create('players_chienbao', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('player1_id');
            $table->unsignedBigInteger('player2_id');
            $table->string('result');
            $table->boolean('win');
            $table->timestamps();

            // Thêm các cột lưu thông số trong trận PK
            $table->json('turns'); // Lưu thông tin lượt đánh của trận PK

        });
    }

    public function down()
    {
        Schema::dropIfExists('players_chienbao');
    }
}
