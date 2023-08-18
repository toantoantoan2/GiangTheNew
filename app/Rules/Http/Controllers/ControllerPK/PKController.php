<?php

namespace App\Http\Controllers\ControllerPK;

use App\TuLuyen\Model_charater;
use App\TuLuyen\Model_chienBao;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PKController
{

    public function index()
    {
        return view('pk.index');
    }
    /**
     * timDoiThu
     *
     * @param  mixed $request
     * @return void
     */
    public function timDoiThu(Request $request)
    {
        $user = $this->checklogin();
        //kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $player1 = $user->get_charaters()->first();
        //kiểm tra tạo nhân vật tu luyện
        if (!$player1) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }
        //kiểm tra lượt pk với tài khoản thường
        if ($player1->luotpk <= 0 && $user->user_vip == 0) {
            flash()->error('Bạn đã hết lượt pk');
            return view('pk.index');
        }

        //kiểm tra xem có đang pk không
        if ($player1->is_pk) {
            flash()->error('Đang pk không thể vui lòng chờ kết thúc');
            return view('pk.index');
        }

        // kiểm tra linh thạch và lấy user khác để pk (chế độ cao cấp)
        if ($player1->pkmode == 1) {
            //kiểm tra linh thạch
            if ($player1->linh_thach < 25) {
                flash()->error('Bạn không đủ 25 linh thạch');
                return view('pk.index');
            }
            //lấy user phòng thủ (chế độ cao cấp)
            $player2 = Model_charater::whereNotIn('id', [$player1->id])
                ->where(function ($query) {
                    $query->where('is_hodao', false)->orWhere(
                        function ($query2) {
                            $query2->where('is_hodao', true)
                                ->where('is_pk', true);
                        }
                    );
                })
                ->where('pkmode', 1)
                ->inRandomOrder()
                ->first();
        } elseif ($player1->pkmode == 0) {
            // lấy user phòng thủ để pk (chế độ thường)
            $player2 = Model_charater::whereNotIn('id', [$player1->id])
                ->where(function ($query) {
                    $query->where('is_hodao', false)->orWhere(
                        function ($query2) {
                            $query2->where('is_hodao', true)
                                ->where('is_pk', true);
                        }
                    );
                })
                ->where('pkmode', 0)
                ->inRandomOrder()
                ->first();
        } else {
            flash()->error('Chế độ pk không hợp lệ');
            return view('pk.index');
        }

        //kiểm tra xem có user phòng thủ nào để pk không
        if (!$player2) {
            if ($player1->pkmode == 1) {
                flash()->error('Không tìm được đối thủ ở chế độ pk cao cấp');
                return view('pk.index');
            } elseif ($player1->pkmode == 0) {
                flash()->error('Không tìm được đối thủ ở chế độ pk thường');
                return view('pk.index');
            } else {
                flash()->error('Chế độ pk không hợp lệ');
                return view('pk.index');
            }
        }
        $user2 = $player2->get_users()->first();
        // dd($user2);

        // trừ 25 linh thạch
        if ($player1->pkmode == 1) {
            $player1->decrement('linh_thach', 25);
        }
        //bắt đầu pk
        $player1->update(['is_pk' => true]);
        //biến tốc độ đánh
        $atkSpeed1 = $player1->atk_speed + $player1->sum_atk_speed; //người tấn công
        $atkSpeed2 = $player2->atk_speed + $player2->sum_atk_speed; //ngường phòng thủ
        // bắt đầu set hp
        $player1->hp = $player1->max_hp + $player1->sum_max_hp;
        $player2->hp = $player2->max_hp + $player2->sum_max_hp;
        // bắt đầu set mp
        $player1->mp = $player1->max_mp + $player1->sum_max_mp;
        $player2->mp = $player2->max_mp + $player2->sum_max_mp;
        //chiến báo
        $chienbao1 = [];
        $chienbao2 = [];
        //kiểm tra xem ai tốc độ đánh lớn hơn thì sẽ đánh trước
        if ($atkSpeed1 > $atkSpeed2) {
            $attacker = $player1;
            $defender = $player2;
        } else {
            $attacker = $player2;
            $defender = $player1;
        }
        $turn = 0;
        $round = 1;

        $tmpturn = [];
        while ($attacker->hp > 0 && $defender->hp > 0) {

            if ($turn == 2) {

                $round += 1;
                $turn = 1;
                $text = "Round " . $round . "\n\n";
                $text2 = "Round " . $round . "\n\n";
            } else {
                $turn += 1;
                $text = "Round " . $round . "\n\n";
                $text2 = "Round " . $round . "\n\n";
            }
            $tmpturn[] = $turn;
            $crit = false;
            $dogge = false;
            // Tính toán số lượt tấn công của người tấn công
            $numAttacks = ceil(($attacker->atk_speed + $attacker->sum_atk_speed) * 100);
            $text .= "Lượt " . $turn . "\n\nBạn tấn công " . $defender->get_users->name . "\n";
            $text2 .= "Lượt " . $turn . "\n\nBạn phòng thủ " . $attacker->get_users->name . "\n";
            $numAttacks = $numAttacks < 1 ? 1 : $numAttacks;
            $text .= "bạn tung ra được $numAttacks đòn đánh trong lượt này";
            $text2 .= "đối phương tung ra $numAttacks đòn đánh trong lượt này";
            // Thực hiện các lượt tấn công của người tấn công
            for ($i = 1; $i < $numAttacks + 1; $i++) {
                $text .= "\nbạn tung ra đòn đánh thứ $i\n";
                $text2 .= "\nđối phương tung ra đòn đánh thứ $i\n";
                // Tính toán sát thương của người tấn công
                $damage = ($attacker->atk + $attacker->sum_atk);
                // Kiểm tra xem người tấn công gây ra sát thương chí mạng hay không
                if (mt_rand(1, 100) <= ($attacker->crit + $attacker->sum_crit) * 100) {
                    $damage *= (1 + ($attacker->crit_dmg + $attacker->sum_crit_dmg)); // Áp dụng sát thương chí mạng
                    $crit = true;
                }
                // Tính phòng thủ của người phòng thủ
                $damage -= ($defender->def + $defender->sum_def);
                if ($damage <= 0) {
                    // Đảm bảo sát thương không nhỏ hơn 0
                    // sát thương nhỏ hơn 0 sẽ dame tối thiểu là 100
                    $damage = 100;
                }
                // Áp dụng khả năng né đòn của người phòng thủ
                if (mt_rand(1, 100) <= ($defender->dodge + $defender->sum_dodge) * 100) {
                    $damage = 0; // Người phòng thủ né đòn thành công
                    $dogge = true;
                }
                //Nếu mp dưới 200 thì dame còn 50%
                if ($attacker->mp < 200 & $damage > 0) {
                    $damage = ceil($damage / 2);
                    $attacker->mp = 0;
                } else {
                    // Trừ 200 mp của người tấn công
                    $attacker->mp -= 200;
                }
                // Trừ điểm máu của người phòng thủ
                $defender->hp -= $damage;

                //turn chiến báo
                $text .= $crit ? "Bạn đã gây sát thương chí mạng" : "Bạn không có chí mạng \n";
                $text .=  $dogge ? "Đối phương đã né tránh sát thương bạn gây ra là: 0" : "Sát thương ban gây ra là: " . $damage;
                $text .= "\n" . 'Hp của "' . $defender->get_users->name . '" còn lại: ' . $defender->hp;


                $text2 .= $dogge ? "Bạn đã né được đòn tấn công sát thương gây ra bởi đối phường = 0" : "bạn không né được sát thương đối phương gây ra là: " . $damage;
                $text2 .= "\n" . 'Hp của bạn còn lại: ' . $defender->hp;

                // Kiểm tra xem người phòng thủ có còn HP không
                if ($defender->hp <= 0) {
                    break 2; // Người phòng thủ đã bị hạ gục, kết thúc vòng lặp
                }
            }

            //kiểm tra xem lượt ai đang đánh để ghi chiến báo
            if ($attacker === $player1) {
                $chienbao1[$round][] = [$text];
                $chienbao2[$round][] = [$text2];
            } else {
                $chienbao1[$round][] = [$text2];
                $chienbao2[$round][] = [$text];
            }
            // Hoán đổi người tấn công và người phòng thủ

            if ($attacker === $player1) {
                $attacker = $player2;
                $defender = $player1;
            } else {
                $attacker = $player1;
                $defender = $player2;
            }
        }

        //debug
        //dd($tmpturn,$chienbao1, $chienbao2);

        // Xác định người thắng cuộc
        if ($player1->hp <= 0) {
            //phòng thủ thắng
            flash()->error('Bạn đã bị "' . $user2->name . '" làm thịt và rớt lại mớ đồ hzzz. Vui lòng xem chiến báo.');
            //ghi chiến báo
            $notify = new Model_chienBao;
            $notify2 = new Model_chienBao;

            //user tấn công
            $notify->user_id = $player1->user_id;
            $notify->player1_id = $player1->user_id;
            $notify->player2_id = $player2->user_id;
            $notify->win = false;
            //user thủ
            $notify2->user_id = $player2->user_id;
            $notify2->player1_id = $player2->user_id;
            $notify2->player2_id = $player1->user_id;
            $notify2->win = true;
            // Trừ tích phân người thua và thêm tích phân cho người thắng
            //kiểm tra tích phân để trừ
            $text = "Bạn đã thua $user2->name (id: $user2->id) \n\n";
            $text2 = "Bạn đã thắng $user->name (id: $user->id) \n\n";
            if ($player1->tichphan < 10) {
                // nhỏ hơn 10 thì trừ toàn bộ tích phân
                $tichphan = $player1->tichphan;
            } elseif ($player1->tichphan < 100) {
                //nhỏ hơn 100 sẽ trừ mỗi lần 10
                $tichphan = 10;
            } else {
                // tính 10% tích phân của người thua để trừ
                $tichphan = $player1->tichphan * 0.1;
            };

            $player1->tichphan -= $tichphan; //trừ tích phân bên thua
            $text .= "bạn bị trừ $tichphan tích phân, bạn còn lại $player1->tichphan tích phân\n";
            //kiểm tra căn cốt nhỏ hơn 5 sẽ set về 0
            if ($player1->can_co < 5) {
                $player1->can_co = 0;
            } else {
                $player1->can_co -= 5; // trừ 5 căn cốt cho người thua
            }
            $text .= "bạn bị trừ 5 căn cốt, bạn còn lại $player1->can_co căn cốt";
            //kiểm tra chế độ pk để + điểm
            if ($player1->pkmode == 0) {
                //pk thường
                $temp = 10 + $tichphan;
                $player2->tichphan += $temp; // +10 tích phân và 10% tích phân cho người thắng
                $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
            } elseif ($player1->pkmode == 1) {
                //pk cao cấp

                $player2->linh_thach += 40; //+40 linh thạch
                $text2 .= "bạn được +40 linh thạch, linh thạch hiện tại là $player2->linh_thach";

                if ($user2->user_vip) {
                    $temp = 90 + $tichphan;
                    $player2->tichphan +=   $temp; //+90 và 10% tích phân
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                } else {
                    $temp = 10 + $tichphan;
                    $player2->tichphan +=   $temp; // +10 tích phân và 10% tích phân cho người thắng
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                }
            }

            $notify->result = $text;
            $notify->turns = json_encode($chienbao1);
            $notify->save();

            $notify2->result = $text2;
            $notify2->turns = json_encode($chienbao2);
            $notify2->save();
        } else {
            //tấn công thắng
            //ghi chiến báo
            $notify = new Model_chienBao;
            $notify2 = new Model_chienBao;

            //user tấn công
            $notify->user_id = $player1->user_id;
            $notify->player1_id = $player1->user_id;
            $notify->player2_id = $player2->user_id;
            $notify->win = true;
            //user thủ
            $notify2->user_id = $player2->user_id;
            $notify2->player1_id = $player2->user_id;
            $notify2->player2_id = $player1->user_id;
            $notify2->win = false;
            flash()->success('Bạn đã giết được "' . $user2->name . '" và cướp được phần thưởng khủng. Vui lòng xem chiến báo.');
            $text = "Bạn đã thua $user->name (id: $user->id) \n\n";
            $text2 = "Bạn đã thắng $user2->name (id: $user2->id) \n\n";

            // Trừ tích phân người thua và thêm tích phân cho người thắng
            //kiểm tra tích phân để trừ
            if ($player2->tichphan < 10) {
                // nhỏ hơn 10 thì trừ toàn bộ tích phân
                $tichphan = $player2->tichphan;
            } elseif ($player2->tichphan < 100) {
                //nhỏ hơn 100 sẽ trừ mỗi lần 10
                $tichphan = 10;
            } else {
                // tính 10% tích phân của người thua để trừ
                $tichphan = $player2->tichphan * 0.1;
            };
            $player2->tichphan -= $tichphan; //trừ tích phân bên thua
            $text .= "bạn bị trừ $tichphan tích phân, bạn còn lại $player2->tichphan tích phân\n";
            //kiểm tra căn cốt nhỏ hơn 5 sẽ set về 0
            if ($player2->can_co < 5) {
                $player2->can_co = 0;
            } else {
                $player2->can_co -= 5; // trừ 5 căn cốt cho người thua
            }
            $text .= "bạn bị trừ 5 căn cốt, bạn còn lại $player1->can_co căn cốt";
            //kiểm tra chế độ pk để + điểm
            if ($player1->pkmode == 0) {
                //pk thường
                $temp = 10 + $tichphan;
                $player1->tichphan += $temp; // +10 tích phân và 10% tích phân cho người thắng
                $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
            } elseif ($player1->pkmode == 1) {
                //pk cao cấp

                $player1->linh_thach += 40; //+40 linh thạch
                $text2 .= "bạn được +40 linh thạch, linh thạch hiện tại là $player2->linh_thach";

                if ($user->user_vip) {
                    $temp = 90 + $tichphan;
                    $player1->tichphan += $temp; //+90 và 10% tích phân
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                } else {
                    $temp = 10 + $tichphan;
                    $player1->tichphan += $temp; // +10 tích phân và 10% tích phân cho người thắng
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                }
            }

            $notify->result = $text;
            $notify->turns = json_encode($chienbao1);
            $notify->save();

            $notify2->result = $text2;
            $notify2->turns = json_encode($chienbao2);
            $notify2->save();
        }

        // set lại hp
        $player1->hp = $player1->max_hp + $player1->sum_max_hp;
        $player2->hp = $player2->max_hp + $player2->sum_max_hp;
        // set lại mp
        $player1->mp = $player1->max_mp + $player1->sum_max_mp;
        $player2->mp = $player2->max_mp + $player2->sum_max_mp;
        //-1 lượt pk
        $player1->luotpk -= 1;
        $player1->is_pk = false;



        //+chiến báo
        $player1->chien_bao += 1;
        $player2->chien_bao += 1;

        //lưu vào db
        $player1->save();
        $player2->save();



        // Trả về kết quả
        return view('pk.index');
    }


    public function timDoiThuId($id)
    {
        $user = $this->checklogin();
        //kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $player1 = $user->get_charaters()->first();
        //kiểm tra tạo nhân vật tu luyện
        if (!$player1) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }
        //kiểm tra lượt pk với tài khoản thường
        if ($player1->luotpk <= 0 && $user->user_vip == 0) {
            flash()->error('Bạn đã hết lượt pk');
            return view('pk.index');
        }

        //kiểm tra xem có đang pk không
        if ($player1->is_pk) {
            flash()->error('Đang pk không thể vui lòng chờ kết thúc');
            return view('pk.index');
        }

        // kiểm tra linh thạch và lấy user khác để pk (chế độ cao cấp)
        if ($player1->pkmode == 1) {
            //kiểm tra linh thạch
            if ($player1->linh_thach < 25) {
                flash()->error('Bạn không đủ 25 linh thạch');
                return view('pk.index');
            }
            //lấy user phòng thủ (chế độ cao cấp)
            $player2 = Model_charater::where('user_id', $id)->first();
        } elseif ($player1->pkmode == 0) {
            // lấy user phòng thủ để pk (chế độ thường)
            $player2 = Model_charater::where('user_id', $id)->first();
        } else {
            flash()->error('Chế độ pk không hợp lệ');
            return view('pk.index');
        }


        if (!$player2) {
            flash()->error('id user không hợp lệ hoặc chưa tu luyện');
            return view('pk.index');
        }
        if ($player1->id === $player2->id) {
            flash()->error('Không thể pk với chính mình');
            return view('pk.index');
        }
        if ($player2->is_hodao) {
        }
        $user2 = $player2->get_users()->first();

        // trừ 25 linh thạch
        if ($player1->linh_thach < 25) {

            flash()->error('Không đủ 25 linh thạch');
            return view('pk.index');
        }
        // kiểm tra chế độ pk của 2 user
        if ($player1->pkmode != $player2->pkmode) {
            flash()->error('Đối phương đang ở chế độ pk khác bạn');
            return view('pk.index');
        }
        if ($player1->pkmode == 1) {
            $player1->decrement('linh_thach', 25);
        }
        //bắt đầu pk
        $player1->update(['is_pk' => true]);
        //biến tốc độ đánh
        $atkSpeed1 = $player1->atk_speed + $player1->sum_atk_speed; //người tấn công
        $atkSpeed2 = $player2->atk_speed + $player2->sum_atk_speed; //ngường phòng thủ
        // bắt đầu set hp
        $player1->hp = $player1->max_hp + $player1->sum_max_hp;
        $player2->hp = $player2->max_hp + $player2->sum_max_hp;
        // bắt đầu set mp
        $player1->mp = $player1->max_mp + $player1->sum_max_mp;
        $player2->mp = $player2->max_mp + $player2->sum_max_mp;
        //chiến báo
        $chienbao1 = [];
        $chienbao2 = [];
        //kiểm tra xem ai tốc độ đánh lớn hơn thì sẽ đánh trước
        if ($atkSpeed1 > $atkSpeed2) {
            $attacker = $player1;
            $defender = $player2;
        } else {
            $attacker = $player2;
            $defender = $player1;
        }
        $turn = 0;
        $round = 1;

        $tmpturn = [];
        while ($attacker->hp > 0 && $defender->hp > 0) {

            if ($turn == 2) {

                $round += 1;
                $turn = 1;
                $text = "Round " . $round . "\n\n";
                $text2 = "Round " . $round . "\n\n";
            } else {
                $turn += 1;
                $text = "Round " . $round . "\n\n";
                $text2 = "Round " . $round . "\n\n";
            }
            $tmpturn[] = $turn;
            $crit = false;
            $dogge = false;
            // Tính toán số lượt tấn công của người tấn công
            $numAttacks = ceil(($attacker->atk_speed + $attacker->sum_atk_speed) * 100);
            $text .= "Lượt " . $turn . "\n\nBạn tấn công " . $defender->get_users->name . "\n";
            $text2 .= "Lượt " . $turn . "\n\nBạn phòng thủ " . $attacker->get_users->name . "\n";
            $numAttacks = $numAttacks < 1 ? 1 : $numAttacks;
            $text .= "bạn tung ra được $numAttacks đòn đánh trong lượt này";
            $text2 .= "đối phương tung ra $numAttacks đòn đánh trong lượt này";
            // Thực hiện các lượt tấn công của người tấn công
            for ($i = 1; $i < $numAttacks + 1; $i++) {
                $text .= "\nbạn tung ra đòn đánh thứ $i\n";
                $text2 .= "\nđối phương tung ra đòn đánh thứ $i\n";
                // Tính toán sát thương của người tấn công
                $damage = ($attacker->atk + $attacker->sum_atk);
                // Kiểm tra xem người tấn công gây ra sát thương chí mạng hay không
                if (mt_rand(1, 100) <= ($attacker->crit + $attacker->sum_crit) * 100) {
                    $damage *= (1 + ($attacker->crit_dmg + $attacker->sum_crit_dmg)); // Áp dụng sát thương chí mạng
                    $crit = true;
                }
                // Tính phòng thủ của người phòng thủ
                $damage -= ($defender->def + $defender->sum_def);
                if ($damage < 0) {
                    // Đảm bảo sát thương không nhỏ hơn 0
                    // sát thương nhỏ hơn 0 sẽ dame tối thiểu là 100
                    $damage = 100;
                }
                // Áp dụng khả năng né đòn của người phòng thủ
                if (mt_rand(1, 100) <= ($defender->dodge + $defender->sum_dodge) * 100) {
                    $damage = 0; // Người phòng thủ né đòn thành công
                    $dogge = true;
                }
                //Nếu mp dưới 200 thì dame còn 50%
                if ($attacker->mp < 200 & $damage > 0) {
                    $damage = ceil($damage / 2);
                    $attacker->mp = 0;
                } else {
                    // Trừ 200 mp của người tấn công
                    $attacker->mp -= 200;
                }
                // Trừ điểm máu của người phòng thủ
                $defender->hp -= $damage;

                //turn chiến báo
                $text .= $crit ? "Bạn đã gây sát thương chí mạng" : "Bạn không có chí mạng \n";
                $text .=  $dogge ? "Đối phương đã né tránh sát thương bạn gây ra là: 0" : "Sát thương ban gây ra là: " . $damage;
                $text .= "\n" . 'Hp của "' . $defender->get_users->name . '" còn lại: ' . $defender->hp;


                $text2 .= $dogge ? "Bạn đã né được đòn tấn công sát thương gây ra bởi đối phường = 0" : "bạn không né được sát thương đối phương gây ra là: " . $damage;
                $text2 .= "\n" . 'Hp của bạn còn lại: ' . $defender->hp;

                // Kiểm tra xem người phòng thủ có còn HP không
                if ($defender->hp <= 0) {
                    break 2; // Người phòng thủ đã bị hạ gục, kết thúc vòng lặp
                }
            }

            //kiểm tra xem lượt ai đang đánh để ghi chiến báo
            if ($attacker === $player1) {
                $chienbao1[$round][] = [$text];
                $chienbao2[$round][] = [$text2];
            } else {
                $chienbao1[$round][] = [$text2];
                $chienbao2[$round][] = [$text];
            }
            // Hoán đổi người tấn công và người phòng thủ

            if ($attacker === $player1) {
                $attacker = $player2;
                $defender = $player1;
            } else {
                $attacker = $player1;
                $defender = $player2;
            }
        }

        //debug
        //dd($tmpturn,$chienbao1, $chienbao2);

        // Xác định người thắng cuộc
        if ($player1->hp <= 0) {
            //phòng thủ thắng
            flash()->error('Bạn đã bị "' . $user2->name . '" làm thịt và rớt lại mớ đồ hzzz. Vui lòng xem chiến báo.');
            //ghi chiến báo
            $notify = new Model_chienBao;
            $notify2 = new Model_chienBao;

            //user tấn công
            $notify->user_id = $player1->user_id;
            $notify->player1_id = $player1->user_id;
            $notify->player2_id = $player2->user_id;
            $notify->win = false;
            //user thủ
            $notify2->user_id = $player2->user_id;
            $notify2->player1_id = $player2->user_id;
            $notify2->player2_id = $player1->user_id;
            $notify2->win = true;
            // Trừ tích phân người thua và thêm tích phân cho người thắng
            //kiểm tra tích phân để trừ
            $text = "Bạn đã thua $user2->name (id: $user2->id) \n\n";
            $text2 = "Bạn đã thắng $user->name (id: $user->id) \n\n";
            if ($player1->tichphan < 10) {
                // nhỏ hơn 10 thì trừ toàn bộ tích phân
                $tichphan = $player1->tichphan;
            } elseif ($player1->tichphan < 100) {
                //nhỏ hơn 100 sẽ trừ mỗi lần 10
                $tichphan = 10;
            } else {
                // tính 10% tích phân của người thua để trừ
                $tichphan = $player1->tichphan * 0.1;
            };

            $player1->tichphan -= $tichphan; //trừ tích phân bên thua
            $text .= "bạn bị trừ $tichphan tích phân, bạn còn lại $player1->tichphan tích phân\n";
            //kiểm tra căn cốt nhỏ hơn 5 sẽ set về 0
            if ($player1->can_co < 5) {
                $player1->can_co = 0;
            } else {
                $player1->can_co -= 5; // trừ 5 căn cốt cho người thua
            }
            $text .= "bạn bị trừ 5 căn cốt, bạn còn lại $player1->can_co căn cốt";
            //kiểm tra chế độ pk để + điểm
            if ($player1->pkmode == 0) {
                //pk thường
                $temp = 10 + $tichphan;
                $player2->tichphan += $temp; // +10 tích phân và 10% tích phân cho người thắng
                $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
            } elseif ($player1->pkmode == 1) {
                //pk cao cấp

                $player2->linh_thach += 40; //+40 linh thạch
                $text2 .= "bạn được +40 linh thạch, linh thạch hiện tại là $player2->linh_thach";

                if ($user2->user_vip) {
                    $temp = 90 + $tichphan;
                    $player2->tichphan +=   $temp; //+90 và 10% tích phân
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                } else {
                    $temp = 10 + $tichphan;
                    $player2->tichphan +=   $temp; // +10 tích phân và 10% tích phân cho người thắng
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                }
            }

            $notify->result = $text;
            $notify->turns = json_encode($chienbao1);
            $notify->save();

            $notify2->result = $text2;
            $notify2->turns = json_encode($chienbao2);
            $notify2->save();
        } else {
            //tấn công thắng
            //ghi chiến báo
            $notify = new Model_chienBao;
            $notify2 = new Model_chienBao;

            //user tấn công
            $notify->user_id = $player1->user_id;
            $notify->player1_id = $player1->user_id;
            $notify->player2_id = $player2->user_id;
            $notify->win = true;
            //user thủ
            $notify2->user_id = $player2->user_id;
            $notify2->player1_id = $player2->user_id;
            $notify2->player2_id = $player1->user_id;
            $notify2->win = false;
            flash()->success('Bạn đã giết được "' . $user2->name . '" và cướp được phần thưởng khủng. Vui lòng xem chiến báo.');
            $text = "Bạn đã thua $user->name (id: $user->id) \n\n";
            $text2 = "Bạn đã thắng $user2->name (id: $user2->id) \n\n";

            // Trừ tích phân người thua và thêm tích phân cho người thắng
            //kiểm tra tích phân để trừ
            if ($player2->tichphan < 10) {
                // nhỏ hơn 10 thì trừ toàn bộ tích phân
                $tichphan = $player2->tichphan;
            } elseif ($player2->tichphan < 100) {
                //nhỏ hơn 100 sẽ trừ mỗi lần 10
                $tichphan = 10;
            } else {
                // tính 10% tích phân của người thua để trừ
                $tichphan = $player2->tichphan * 0.1;
            };
            $player2->tichphan -= $tichphan; //trừ tích phân bên thua
            $text .= "bạn bị trừ $tichphan tích phân, bạn còn lại $player2->tichphan tích phân\n";
            //kiểm tra căn cốt nhỏ hơn 5 sẽ set về 0
            if ($player2->can_co < 5) {
                $player2->can_co = 0;
            } else {
                $player2->can_co -= 5; // trừ 5 căn cốt cho người thua
            }
            $text .= "bạn bị trừ 5 căn cốt, bạn còn lại $player1->can_co căn cốt";
            //kiểm tra chế độ pk để + điểm
            if ($player1->pkmode == 0) {
                //pk thường
                $temp = 10 + $tichphan;
                $player1->tichphan += $temp; // +10 tích phân và 10% tích phân cho người thắng
                $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
            } elseif ($player1->pkmode == 1) {
                //pk cao cấp

                $player1->linh_thach += 40; //+40 linh thạch
                $text2 .= "bạn được +40 linh thạch, linh thạch hiện tại là $player2->linh_thach";

                if ($user->user_vip) {
                    $temp = 90 + $tichphan;
                    $player1->tichphan += $temp; //+90 và 10% tích phân
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                } else {
                    $temp = 10 + $tichphan;
                    $player1->tichphan += $temp; // +10 tích phân và 10% tích phân cho người thắng
                    $text2 .= "bạn được +$temp tích phân, tích phân hiện tại là $player2->tichphan\n";
                }
            }

            $notify->result = $text;
            $notify->turns = json_encode($chienbao1);
            $notify->save();

            $notify2->result = $text2;
            $notify2->turns = json_encode($chienbao2);
            $notify2->save();
        }

        // set lại hp
        $player1->hp = $player1->max_hp + $player1->sum_max_hp;
        $player2->hp = $player2->max_hp + $player2->sum_max_hp;
        // set lại mp
        $player1->mp = $player1->max_mp + $player1->sum_max_mp;
        $player2->mp = $player2->max_mp + $player2->sum_max_mp;
        //-1 lượt pk
        $player1->luotpk -= 1;
        $player1->is_pk = false;



        //+chiến báo
        $player1->chien_bao += 1;
        $player2->chien_bao += 1;

        //lưu vào db
        $player1->save();
        $player2->save();



        // Trả về kết quả
        return view('pk.index');
    }


    public function packTuLuyen($pack)
    {
        //pack = 1 gói cơ bản
        //pack = 2 gói bình dân
        //pack = 3 gói
        $user = $this->checklogin();
        //Kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $player = $user->get_charaters()->first();
        //Kiểm tra xem tạo nhân vật chưa
        if (!$player) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }
        //số linh thạch cần để mua
        $linhThach = [1 => 500, 2 => 1000, 3 => 2000];

        if ($player->linh_thach < $linhThach[$pack]) {

            flash()->error('Bạn không đủ linh thạch để mua gói đã chọn');
            return view('pk.index');
        }
        // dd($linhThach[$pack]);
        switch ($pack) {
            case 1:
                if ($player["pack_" . $pack] >= 4) {
                    flash()->error("Bạn mua 4 gói cơ bản trong tháng này nên không thể mua thêm.");
                    return view('pk.uu_dai_tu_luyen');
                }
                $player->update([
                    "exp" => $player->exp + 60000, //cộng 60k exp
                    "sum_max_hp" => $player->sum_max_hp + 400, // cộng 400 hp
                    "sum_max_mp" => $player->sum_max_mp + 400, //cộng 400 mp
                    "sum_atk" => $player->sum_atk + 120, //cộng 120 sát thương
                    "sum_def" => $player->sum_def + 80, //cộng 80 phòng thủ
                    "pack_" . $pack => $player["pack_$pack"] + 1, //cộng vào gói pack
                    "linh_thach" => $player->linh_thach -  $linhThach[$pack], //trừ linh thạch
                ]);

                flash()->success("Bạn mua thành công gói tu luyện cơ bản\\n\\n Tăng 120 sát thương, 80 phòng thủ, 60k tu vi, 400 hp, 400 mp");
                return view('pk.uu_dai_tu_luyen');
                break;
            case 2:
                if ($player["pack_" . $pack] >= 4) {
                    flash()->error("Bạn mua 4 gói bình dân trong tháng này nên không thể mua thêm.");
                    return view('pk.uu_dai_tu_luyen');
                }
                $player->update([
                    "exp" => $player->exp + 120000, //cộng 120k exp
                    "sum_max_hp" => $player->sum_max_hp + 900, // cộng 900 hp
                    "sum_max_mp" => $player->sum_max_mp + 900, //cộng 900 mp
                    "sum_atk" => $player->sum_atk + 250, //cộng 250 sát thương
                    "sum_def" => $player->sum_def + 180, //cộng 180 phòng thủ
                    "pack_" . $pack => $player["pack_$pack"] + 1, //cộng vào gói pack
                    "linh_thach" => $player->linh_thach -  $linhThach[$pack], //trừ linh thạch
                ]);
                flash()->success("Bạn mua thành công gói tu luyện bình dân\\n\\n Tăng 250 sát thương, 180 phòng thủ, 120k tu vi, 900 hp, 900 mp");
                return view('pk.uu_dai_tu_luyen');
                break;
            case 3:

                $topPlayers = Model_charater::where('top', '>', 0)->where('top', '<=', 10)
                    ->take(10)
                    ->get();
                // Kiểm tra xem người chơi cụ thể có nằm trong top 10 hay không
                $isInTop10 = $topPlayers->contains(function ($playerCheck) use ($player) {
                    return $playerCheck->id === $player->id;
                });
                //biến $isDate để kiểm tra xem có tạo nhân vật dưới 30 ngày không
                $isDate = $player->created_at > Carbon::now()->subDays(30);
                //kiểm tra xem có nằm trong top mười, tạo dưới 30 ngày, là vip hay không
                if (!$isInTop10 && !$isDate && !$user->user_vip) {
                    flash()->error('Bạn không đủ điều kiện mua gói này');
                    return view('pk.uu_dai_tu_luyen');
                }
                //kiểm tra xem các gói có được mua chưa
                if (
                    ($isDate && $player->is_packnew) &&
                    ($isInTop10 && $player->is_packtop) &&
                    ($user->user_vip && $player->is_packvip)
                ) {
                    flash()->error('Bạn mua đủ gói tu luyện super trong tháng này');
                    return view('pk.uu_dai_tu_luyen');
                }
                // biến lưu lại chỉ số +
                $add = [
                    "exp" => $player->exp + 500000, //cộng 500k exp
                    "sum_max_hp" => $player->sum_max_hp + 4500, // cộng 900 hp
                    "sum_max_mp" => $player->sum_max_mp + 4500, //cộng 900 mp
                    "sum_atk" => $player->sum_atk + 1500, //cộng 250 sát thương
                    "sum_def" => $player->sum_def + 1000, //cộng 180 phòng thủ
                    "pack_" . $pack =>  $player["pack_$pack"] + 1, //cộng vào gói pack
                    "linh_thach" => $player->linh_thach -  $linhThach[$pack], //trừ linh thạch

                ];

                //kiểm tra nếu tạo nhân vật dưới 30 và chua mua thì mua cái này
                if ($isDate && !$player->is_packnew) {
                    $add['is_packnew'] =  true;

                    $player->update($add);
                    flash()->success("Bạn mua thành công gói tu luyện supper khi tạo nhân vật dưới 30 ngày\\n\\nTăng 1500 sát thương, 1000 phòng thủ, 500k tu vi, 4500 hp, 4500 mp");
                    return view('pk.uu_dai_tu_luyen');
                }

                //kiểm tra nếu là vip và tháng này chưa mua thì sẽ mua gói này
                elseif ($user->user_vip  && !$player->is_packvip) {
                    $add['is_packvip'] =  true;
                    $player->update($add);
                    flash()->success("Bạn mua thành công gói tu luyện supper  với tài khoản vip\\n\\nTăng 1500 sát thương, 1000 phòng thủ, 500k tu vi, 4500 hp, 4500 mp");
                    return view('pk.uu_dai_tu_luyen');
                }

                //kiểm tra nếu nằm trong top 10 và tháng này chưa mua thì sẽ mua gói này
                elseif ($isInTop10 && !$player->is_packtop) {
                    // + chỉ số
                    $add['is_packtop'] =  true;
                    //thêm gói top tháng này mua rồi
                    $player->update($add);
                    flash()->success("Bạn mua thành công gói tu luyện supper khi bạn nằm trong top 10\\n\\nTăng 1500 sát thương, 1000 phòng thủ, 500k tu vi, 4500 hp, 4500 mp");
                    return view('pk.index');
                } else {
                    flash()->error('Bạn mua đủ gói tu luyện supper theo yêu cầu trong tháng này');
                    return view('pk.uu_dai_tu_luyen');
                }
                break;
            default:


                flash()->error('Chọn gói tu luyện không hợp lệ');

                return view('pk.uu_dai_tu_luyen');

                break;
        }

        return view('pk.uu_dai_tu_luyen');
    }

    public function packTuLuyenIndex()
    {
        return view('pk.uu_dai_tu_luyen');
    }
    public function packKhivan($pack)
    {
        //pack = 1 gói 1 tháng
        //pack = 2 gói 3 tháng
        //pack = 3 gói 6 tháng
        $user = $this->checklogin();
        //Kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $player = $user->get_charaters()->first();
        //Kiểm tra xem tạo nhân vật chưa
        if (!$player) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }
        //số linh thạch cần để mua
        $linhThach = [1 => ["lt" => 1000, "m" => 1], 2 => ["lt" => 2850, "m" => 3], 3 => ["lt" => 5400, "m" => 6]];
        // dd($player->linh_thach,$linhThach[$pack]["lt"]);

        if ($player->is_auto) {
            flash()->error('Bạn đang còn thời gian auto không thể mua thêm');
            return view('pk.uu_dai_khi_van');
        }
        if ($player->is_auto) {
            flash()->error('Bạn đang còn thời gian auto không thể mua thêm');
            return view('pk.uu_dai_khi_van');
        }
        $now = Carbon::now();
        if ($player->date_auto > $now) {
            flash()->error('Bạn đang còn thời gian auto không thể mua thêm');
            return view('pk.uu_dai_khi_van');
        }
        if ($player->linh_thach < $linhThach[$pack]["lt"]) {

            flash()->error('Bạn không đủ linh thạch để mua gói auto đã chọn');
            return view('pk.uu_dai_khi_van');
        }
        $add = [
            "is_auto" => true,
            "linh_thach" => $player->linh_thach - $linhThach[$pack]["lt"],
        ];
        switch ($pack) {

            case 1:
                $add["date_auto"] = $now->addMonths($linhThach[$pack]["m"]);
                break;
            case 2:
                $add["date_auto"] = $now->addMonths($linhThach[$pack]["m"]);
                break;
            case 3:
                $add["date_auto"] = $now->addMonths($linhThach[$pack]["m"]);
                break;
            default:
                flash()->error('Gói auto không chính xác');
                return view('pk.uu_dai_khi_van');
        }
        $player->update($add);
        flash()->success("Bạn đã mua thành công gói auto " . $linhThach[$pack]["m"] . " tháng. Sử dụng auto đến ngày: $player->date_auto");
        return view('pk.uu_dai_khi_van');
    }
    public function packKhivanIndex()
    {
        return view('pk.uu_dai_khi_van');
    }

    public function pkModeThuong()
    {
        $user = $this->checklogin();
        //Kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $player = $user->get_charaters()->first();
        //Kiểm tra xem tạo nhân vật chưa
        if (!$player) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }
        //Kiểm tra xem có phải đang ở chế độ thường
        if ($player->pkmode == 0) {
            flash()->error('Hiện tại đang ở chế độ pk thường');
            return view('pk.index',compact('player', 'user'));
        } else {
            flash()->success('Chuyển sang chế độ pk thường');
            $player->update(['pkmode' => 0]);
            return view('pk.index',compact('player', 'user'));
        }
    }

    public function checkFirstTime()
    {
        $user = $this->checklogin();

        $player = $user->get_charaters()->first();

        if (!$player) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }

        if ($player->pk_firsttime == 1) {
            $player->tichphan += 100;
            $player->pk_firsttime = 0;
            $player->pkmode == 0;
            $player->save();
        }
        return redirect()->route('pk.index');
    }


    public function pkModeCaoCap()
    {
        $user = $this->checklogin();
        //Kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $player = $user->get_charaters()->first();
        //Kiểm tra xem tạo nhân vật chưa
        if (!$player) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }
        //Kiểm tra xem có phải đang ở chế cao cấp
        if ($player->pkmode == 1) {
            flash()->error('Hiện tại đang ở chế độ pk cao cấp');
            return view('pk.index',compact('player', 'user'));
        } else {
            flash()->success('Chuyển sang chế độ pk cao cấp');
            $player->update(['pkmode' => 1]);
            return view('pk.index',compact('player', 'user'));
        }
    }

    public function listChienBao()
    {
        $user = $this->checklogin();
        //Kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $listCB = $user->get_chienbao()->take(100)->orderByDesc('created_at')->get();
        return $listCB;
    }
    public function showChienBao($id)
    {
        $user = $this->checklogin();
        //Kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $listCB = $user->get_chienbao()->where('id', $id)->where('user_id', $user->id)->first();
        return $listCB;
    }

    public function muaHoDao()
    {
        $user = $this->checklogin();
        $linhThach = 1000; // số linh thạch cần để mua hộ đạo
        //Kiểm tra đăng nhập
        if (!$user) {
            flash()->error('Bạn chưa đăng nhập');
            return redirect()->route('home');
        }
        $player = $user->get_charaters()->first();
        //Kiểm tra xem tạo nhân vật chưa
        if (!$player) {
            flash()->error('Bạn chưa tạo nhân vật');
            return redirect()->route('tuluyen.create');
        }

        //Kiểm tra xem đã mua hộ đạo chưa
        if ($player->is_hodao) {
            flash()->error('Thời gian hộ đạo đang còn');
            return redirect()->route('pk.index');
        }
        $now = Carbon::now();
        if ($player->date_hodao > $now) {
            flash()->error('Thời gian hộ đạo đang còn');
            return redirect()->route('pk.index');
        }
        //kiểm tra xem có đủ linh thạch không
        if ($player->linh_thach < $linhThach) {
            flash()->error('Bạn không đủ linh thạch để mua hộ đạo');
            return redirect()->route('pk.index');
        }
        $player->update([
            "is_hodao" => true,
            "date_hodao" => $now->addMonth(),
            "linh_thach" => $player->linh_thach - $linhThach
        ]);
        flash()->success('Bạn đã mua hộ đạo đến ngày: ' . $player->date_hodao);

        return view('pk.index');
    }


    protected function checklogin()
    {
        if (!auth()->guard('web')->check()) {
            return false;
        } else {
            return auth()->guard('web')->user();
        }
    }
}
