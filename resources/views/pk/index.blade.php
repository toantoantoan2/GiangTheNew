@extends('shop.layouts.app')
@section('content')
<div class="container pk-page">
<div class="row top-pk">
<h2 style="padding-bottom: 14px;"> THIÊN NGOẠI CHIẾN TRƯỜNG </h2>
</div>
<div class="row">
<div class="row body-top-pk">
    <div class="col-12">
        <h6 style="margin-top:10px;">Chiến tích</h6>
    </div>
    <div class="col-12">
        <p style="margin-bottom: 0.1rem;">- Xếp hạng:</p>
    </div>
    <div class="col-12">
        <p style="margin-bottom: 0.1rem;">- Số trận thắng thua:</p>
    </div>
    <div class="col-12">
        <p style="margin-bottom: 0.1rem;">- Số tích phân đang có:</p>
    </div>
</div>
<div class="row body-mid-pk">
    <div class="col-12">
    <p> Chế độ PK:   Có 2 chế độ Thường và Cao cấp
       <br> - Khi ở chế độ thường hay cao cấp, bạn đều có thể chủ động đi PK hoặc được hệ thống phân phối làm đối thủ của người khác.
       <br> - Nếu bạn không muốn bị động bị hệ thống phân phối làm đối thủ của người khác thì có thể thuê người hộ đạo ở phần Shop.
       <br> - Khi thắng ở chế độ thường sẽ chỉ nhận điểm tích phân và không có phần quà khác. Khi thua sẽ bị mất 5 điểm căn cốt.
       <br> - Ở chế độ cao cấp, bạn cần 25 linh thạch mỗi lần PK. Thắng bạn sẽ nhận được gấp 9 lần điểm tích phân so với chế độ thường và 40 linh thạch. Thua bạn sẽ bị mất 25 linh thạch dùng để tham gia PK, mất điểm tích phân và mất 5 điểm căn cốt.
       <br> - Mỗi ngày user thường sẽ có 10 lượt chủ động PK thường, user VIP sẽ có 20 lượt chủ động PK thường. Các user có vô hạn lượt PK cao cấp. 
       <br> - Ấn vào nút "Thêm 5 lượt PK" để có thêm 5 lượt PK thường mới.</p>
    </div>
</div>
<div class="row body-bot-pk">
    <div class="col-6 pt-4" style="padding-left: 0;">
        <button type="button" class="btn btn-gt gift btn-pk" data-bs-toggle="modal" data-bs-target="#tim-kiem">Tìm đối thủ
        </button>
    </div>
    <div class="col-6 pt-4" style="padding-right: 0;">
        <button type="button" class="btn btn-gt gift btn-pk" data-bs-toggle="modal" data-bs-target="#tutorial">Hướng dẫn
        </button>
    </div>
    <div class="col-6 pt-4 pb-4" style="padding-left: 0;">
        <button type="button" class="btn btn-gt gift btn-pk" data-bs-toggle="modal" data-bs-target="#shop">Shop
        </button>
    </div>
    <div class="col-6 pt-4 pb-4" style="padding-right: 0;">
        <button type="button" class="btn btn-gt gift btn-pk"  data-bs-toggle="modal" data-bs-target="#change-mod">Đổi chế độ Pk
        </button>
            <p style="color:white; font-weight:400;"> ( Thường ) </p>
    </div>

</div>

 <!-- modal shop -->
 <div class="modal fade" id="shop" tabindex="-1" aria-labelledby="exampleModalLabel"
 aria-hidden="true" style="font-size: 16px;">
 <div class="modal-dialog modal-dialog-centered modal-md">
     <div class="modal-content" style="background: #3c4f79;
     border: solid #cdcd1b 1px;">
         <div class="modal-header" style="flex-direction: column-reverse;">
             <h6 class="modal-title" style="    line-height: 0;
             font-weight: 600;
             color: #ffea1f;
             font-size: 18px;">Shop</h6>
             <button style="    padding: 0rem 1rem;
             background: none;
             color: white;
             font-weight: 600;
             opacity: 15;" type="button" class="btn-close" data-bs-dismiss="modal"
                     aria-label="Close">X</button>
         </div>
         <div class="modal-body" style="color: white;">
             <form method="post" action="" style="text-align:left">
                 @csrf
                 <p style="font-weight:400;">Đã mua: </p>
                 <p style="font-weight: 400;
                            margin-top: -15px;">Thời gian còn lại: </p>

                 <div class="row" style="border-bottom: 1px dashed #dee2e6;
                 border-top: 1px dashed #dee2e6;
                 padding-top: 18px;
                 padding-bottom: 18px;">

                    <div class="col-3" style="display: flex;
                    align-items: center;
                    justify-content: end;">
                    <img src="\frontend\images\mobile\nguoihodao.png" alt="no-image" class="" style="height: 50px;
                    width: 50px;"/>

                    </div>
                    <div class="col-9">
                        <p class="fix-p" style="font-weight:600;"> Người hộ đạo</p>
                        <p class="fix-p" style="font-weight:400;"> Khi sử dụng sẽ không bị người khác đánh lén trong vòng 30 ngày tới </p>
                        <p class="fix-p" style="font-weight: 400;
                        display: inline-block;
                        margin-top: 19px;"> Giá: 1000     <img src="\frontend\images\mobile\linhthach.png" alt="no-image" class="" style="height: 20px; width: 25px;"/>
 </p>
                        <button data-bs-toggle="modal" data-bs-target="#in-pk" type="button" style="border-radius: 20px;
                        display: inline-block;
                        float: right;
                        margin-top: 16px;
                        width: 50%;
                        height: 43px;" class="btn btn-gt gift" data-wallet="">Mua
                        </button>
                    </div>

                </div>

             </form>
             <div class="modal-footer" style="border-top:none;display: flex; justify-content: center;">
            </div>
         </div>
     </div>
 </div>
 </div>

  <!-- huong dan -->
  <div class="modal fade" id="tutorial" tabindex="-1" aria-labelledby="exampleModalLabel"
  aria-hidden="true" style="font-size: 16px;">
  <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content" style="background: #3c4f79;
      border: solid #cdcd1b 1px;">
          <div class="modal-header" style="flex-direction: column-reverse;">
              <h6 class="modal-title" style="    line-height: 0;
              font-weight: 600;
              color: #ffea1f;
              font-size: 18px;">Hướng dẫn</h6>
              <button style="    padding: 0rem 1rem;
              background: none;
              color: white;
              font-weight: 600;
              opacity: 15;" type="button" class="btn-close" data-bs-dismiss="modal"
                      aria-label="Close">X</button>
          </div>
          <div class="modal-body" style="color: white;">
            <p>- Ở Thiên Ngoại Chiến Trường, người chơi có thể thỏa sức mà PK với người chơi khác </p>
            <br>
            <p> <b>1. Chế độ PK:   Có 2 chế độ Thường và Cao cấp </b>
                <br>- Khi ở chế độ thường hay cao cấp, bạn đều có thể chủ động đi PK hoặc được hệ thống phân phối làm đối thủ của người khác.
               <br> - Nếu bạn không muốn bị động bị hệ thống phân phối làm đối thủ của người khác thì có thể thuê người hộ đạo ở phần Shop.
               <br> - Khi thắng ở chế độ thường sẽ chỉ nhận điểm tích phân và không có phần quà khác. Khi thua sẽ bị mất 5 điểm căn cốt.
               <br> - Ở chế độ cao cấp, bạn cần 25 linh thạch mỗi lần PK. Thắng bạn sẽ nhận được gấp 9 lần điểm tích phân so với chế độ thường và 40 linh thạch. Thua bạn sẽ bị mất 25 linh thạch dùng để tham gia PK, mất điểm tích phân và mất 5 điểm căn cốt.
               <br> - Mỗi ngày user thường sẽ có 10 lượt chủ động PK thường, user VIP sẽ có 20 lượt chủ động PK thường. Các user có vô hạn lượt PK cao cấp. 
               <br> - Ấn vào nút "Thêm 5 lượt PK" để có thêm 5 lượt PK thường mới. </p>
               <br>
            <p> <b>2. Điểm tích phân nhận sau PK: </b>
                <br> - Thắng: Nhận 10 tích phân từ hệ thống và 10% tích phân của user thua.
                <br> - Thua: Mất 10% tích phân đang có.
                <br> - Lưu ý: User thắng ở chế độ cao cấp sẽ nhận được 90 tích phân từ hệ thống và 10% tích phân của user thua.  </p>
                <br>
            <p><b> 3. Thưởng Thần Long Bảng:</b>
                <br> - Top 10 Thần Long Bảng sau mỗi tháng sẽ được quyền mua 1 gói SUPER trong gói ưu đãi tu luyện vào tháng tiếp theo.
                <br> - Những đặc quyền khác sẽ đợi quá trình phát triển sau</p>

          </div>
      </div>
  </div>
  </div>

     <!-- modal search -->
     <div class="modal fade" id="change-mod" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true" style="font-size: 16px;">
     <div class="modal-dialog modal-dialog-centered modal-md">
         <div class="modal-content" style="background: #596785;
         border: solid #cdcd1b 1px;">
             <div class="modal-header" style="flex-direction: column-reverse;
             border-bottom: none;">
                 <h6 class="modal-title" style="line-height: 0;
                 font-weight: 600;
                 color: #ffea1f;
                 font-size: 18px;">Đổi chế độ PK</h6>
                 <button style="    padding: 0rem 1rem;
                 background: none;
                 color: white;
                 font-weight: 600;
                 opacity: 15;" type="button" class="btn-close" data-bs-dismiss="modal"
                         aria-label="Close">X</button>
             </div>
             <div class="modal-body" style="
                display: flex;
                justify-content: center;">
                 <form method="post" action="" style="text-align:left">
                     @csrf
                    </form>
                     </div>

                 <div class="row" style="justify-content: center;
                 margin-bottom: 23px;">
                    <div class="col-5">
                     <button type="button" style="border-radius: 20px; background-color:#a7a7a7!important; width:100%" class="btn btn-gt gift" data-wallet="">Thường
                     </button>
                    </div>
                    <div class="col-5">
                     <button type="button" style="border-radius: 20px; width:100%" class="btn btn-gt gift" data-wallet="">Cao Cấp
                     </button>
                    </div>
                    </div>
             </div>
         </div>
     </div>
     </div>


<div class="row table-than-long" style="">
    <button style="margin-top: -36px!important;" type="button" class="btn btn-gt gift than-long-button">Thần long bảng
    </button>
    <div class="col-12" style="padding-left: 0;">

        <div class="row table-than-long-element">

            <div class="col-3 than-long-user">
            <p class="fix-p" style="font-weight:bold;"> 1. </p>
            <img src="uploads/user/no-user.png" alt="no-image" class="comment-avatar" style="margin-left: 0.8rem;"/>

            </div>
            <div class="col-7">
                <p class="fix-p" style="font-weight:500;"> Toàn </p>
                <p class="fix-p" style="font-weight:500;"> tôi tên là thỏ tôi không... </p>
            </div>
            <div class="col-2 than-long-user">
                <p class="fix-p" style="font-weight:500;"> 15.2M </p>
            </div>

        </div>



        </div>

</div>
</div>
</div>

@endsection
