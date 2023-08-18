<?php

namespace App\TuLuyen;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
class Model_charater extends Model
{
    use UuidTrait;

    public $guarded = [];
    public $table = 'players_charater';
    public $primaryKey  = 'id';
    public $keyType = 'string';
    public $incrementing = false;
    // protected  $with = ['get_items'];
    protected $connection = 'dbuser';
    public function get_users()
    {
        return $this->belongsTo('App\User','user_id','id');
    }
    public function get_items()
    {
        return $this->hasMany('App\TuLuyen\Model_item','player_id','id');
    }
}
