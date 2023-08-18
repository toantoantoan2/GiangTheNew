<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumPlayersCharater extends Migration
{
    public function up()
    {
        if (Schema::connection('dbuser')->hasTable('players_charater')) {
            if (!Schema::connection('dbuser')->hasColumns(
                'players_charater',
                [
                    'top', // lưu top tháng trước
                    'pack_1', //lưu số gói cơ bản user mua
                    'pack_2', //lưu số bình dân bản user mua
                    'pack_3', //lưu số gói supper user mua
                    'is_packnew', //kiểm tra xem mua gói mới tạo.
                    'is_packtop', //kiểm tra xem mua gói top tháng này chưa.
                    'is_packvip', //kiểm tra xem vip mua gói tháng này chưa

                ]
            )) {
                Schema::connection('dbuser')->table('players_charater', function (Blueprint $table) {
                    $table->unsignedBigInteger('top')->default(0);
                    $table->tinyInteger('pack_1', false, true)->default(0);
                    $table->tinyInteger('pack_2', false, true)->default(0);
                    $table->tinyInteger('pack_3', false, true)->default(0);
                    $table->boolean('is_packnew')->default(false);
                    $table->boolean('is_packtop')->default(false);
                    $table->boolean('is_packvip')->default(false);
                });
            }
        }
    }
}
