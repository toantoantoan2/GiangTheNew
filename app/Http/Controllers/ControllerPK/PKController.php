<?php

namespace App\Http\Controllers\ControllerPK;

use Illuminate\Http\Request;

class PKController
{

    public function index()
    {
       return view('pk.index');

    }

    public function packTuLuyen() {
        return view('pk.uu_dai_tu_luyen');
    }

    public function packKhivan() {
        return view('pk.uu_dai_khi_van');
    }



}
