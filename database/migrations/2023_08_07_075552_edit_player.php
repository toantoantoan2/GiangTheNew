<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditPlayer extends Migration
{
    public function up()
    {
        if (Schema::connection('dbuser')->hasTable('players_charater')) {
            if (!Schema::connection('dbuser')->hasColumns(
                'players_charater',
                [
                    'tichphan',
                    'luotpk',
                    'is_hodao',
                     'date_hodao',
                     'is_pkconfirm',
                     'pkmode',// 0 là pk thường, 1 là pk cao cấp, sau này thêm gì thì không biết
                     'is_pk',
                ]
            ))
            {
                Schema::connection('dbuser')->table('players_charater', function (Blueprint $table) {
                    $table->boolean('is_hodao')->default(false);
                    $table->integer('tichphan', false, true)->default(0);
                    $table->integer('luotpk', false, true)->default(0);
                    $table->dateTime('date_hodao')->nullable(true);
                    $table->boolean('is_pkconfirm')->default(false);// để xác định lần đầu tiên vào pk sẽ cho 100 tích phân
                    $table->tinyInteger('pkmode',false,true)->default(0);
                    $table->boolean('is_pk')->default(false);
                });
            }
        }
    }
}
