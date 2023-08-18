@extends('shop.layouts.app')
@section('content')
<div class="container">
<div class="row">
    <div class="col-12" >
        <h5 class="pt-3 pb-3" style="text-align:center; border-bottom: solid #ddd9d9 2px; color: #BB2D3B">Gói ưu đãi </h5>
    </div>
    <div class="col-4 pt-2 pb-2" >
        <a href="{{route('pk.packTuLuyen')}}"> <button  type="button" style="border-radius: 20px; height: 40px;" class="btn btn-gt gift" data-wallet=""> Gói tu luyện
        </button> </a>
    </div>
    <div class="col-8 pt-2 pb-2" >
        <a href="{{route('pk.packKhiVan')}}">  <button  type="button" style="border-radius: 20px; height: 40px; background-color:green!important;" class="btn btn-gt gift" data-wallet=""> Khí vận + Auto nhặt quà
        </button>  </a>
    </div>
    <div class="col-12 pb-2" >
        <h6> Gói khí vận + auto nhặt quà</h6>
        <p style="color:red;">
            Tác dụng gói:
           <br> 1. Full khí vận trong 30 ngày.
           <br> 2. Chạy tự động nhặt quà, chạy ngầm không ảnh hưởng đến quá trình user sử dụng.
        </p>
        <p style="font-weight: 450;">
            <br>  - Gói 1 tháng: 1.000 linh thạch/tài khoản/tháng.
            <br> - Gói 3 tháng: 2.850 linh thạch/tài khoản/tháng.
            <br>  - Gói 6 tháng: 5.400 linh thạch/tài khoản/tháng
        </p>
    </div>
    <div class="col-12 pb-2"  >
        <button  type="button" style="width: 100%; height: 40px;" class="btn btn-gt gift" data-wallet="">Mua 1 tháng: 1.000 linh thạch
        </button>
    </div>
    <div class="col-12 pb-2" >
        <button  type="button" style="width: 100%; height: 40px;" class="btn btn-gt gift" data-wallet="">Mua 3 tháng: 2.850 linh thạch
        </button>
    </div>
    <div class="col-12" >
        <button  type="button" style="width: 100%; height: 40px;" class="btn btn-gt gift" data-wallet="">Mua 6 tháng: 5.400 linh thạch
        </button>
    </div>
    <div class="col-12" >
        <p class="mt-2" style="text-align:center; height: 40px;">Thời gian còn lại: 160 tiếng</p>
    </div>
</div>
</div>
</div>

@endsection
