<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumPlayersCharaterAutoKhiVan extends Migration
{
    public function up()
    {
        if (Schema::connection('dbuser')->hasTable('players_charater')) {
            if (!Schema::connection('dbuser')->hasColumns(
                'players_charater',
                [
                    'is_auto', // bật tắt chế độ auto
                    'date_auto', //thời gian hết hạn auto

                ]
            )) {
                Schema::connection('dbuser')->table('players_charater', function (Blueprint $table) {
                    $table->boolean("is_auto")->default(false);
                    $table->dateTime('date_auto')->nullable(true);
                });
            }
        }
    }
}
