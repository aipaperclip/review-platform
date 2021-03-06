<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Mail;
use Auth;
use Image;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\UserInvite;
use App\Models\Vox;
use App\Models\DcnReward;
use App\Models\DcnCashout;
use App\Models\Dcn;
use App\Models\Country;
use App\Models\Civic;
use Carbon\Carbon;

class ProfileController extends FrontController {

    public function setGrace($locale=null) {

        if(empty($this->user->grace_end)) {
            $this->user->grace_end = Carbon::now();
            $this->user->save();
        }
        session(['new_auth' => null]);
    }

    public function vox($locale=null) {

        if(!empty($this->user)) {

            if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_by_clinic_claimed' && $this->user->status!='test') {
                return redirect(getLangUrl('welcome-to-dentavox'));
            }

            $current_ban = $this->user->isBanned('vox');
            $prev_bans = null; 
            $time_left = '';

            $ban_reason = '';
            $ban_alternatives = '';
            $ban_alternatives_buttons = '';

            if( $current_ban ) {

                $prev_bans = $this->user->getPrevBansCount('vox', $current_ban->type);
                if($current_ban->type=='mistakes') {
                    $ban_reason = trans('vox.page.bans.banned-mistakes-title-'.$prev_bans);
                } else {
                    $ban_reason = trans('vox.page.bans.banned-too-fast-title-'.$prev_bans);
                }

                if($prev_bans==1) {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-1');
                    $ban_alternatives_buttons = '
                    <a href="https://dentacare.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                    </a>';
                } else if($prev_bans==2) {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-2');
                    $ban_alternatives_buttons = '
                    <a href="https://reviews.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-trp.png').'" />
                    </a>';
                } else if($prev_bans==3) {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-3');
                    $ban_alternatives_buttons = '
                    <a href="https://dentacare.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                    </a>';
                } else {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-4');
                    $ban_alternatives_buttons = '
                    <a href="https://dentacare.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                    </a>
                    <a href="https://reviews.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-trp.png').'" />
                    </a>';
                }

                if( $current_ban->expires ) {
                    $now = Carbon::now();
                    $time_left = $current_ban->expires->diffInHours($now).':'.
                    str_pad($current_ban->expires->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
                    str_pad($current_ban->expires->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);
                } else {
                    $time_left = null;
                }
            }

            $more_surveys = false;
            $rewards = DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('type', 'survey')->where('reference_id', '!=', 34)->get();
            
            if ($rewards->count() == 1 && $rewards->first()->vox_id == 11) {
                $more_surveys = true;
            }

            $params = [
                'latest_voxes' => Vox::where('type', 'normal')->orderBy('created_at', 'desc')->take(3)->get(),
                'more_surveys' => $more_surveys,
                'prev_bans' => $prev_bans,
                'current_ban' => $current_ban,
                'ban_reason' => $ban_reason,
                'ban_alternatives' => $ban_alternatives,
                'ban_alternatives_buttons' => $ban_alternatives_buttons,
                'time_left' => $time_left,
                'histories' => $this->user->vox_rewards->where('reference_id', '!=', 34),
                'payouts' => $this->user->history->where('type', '=', 'vox-cashout'),
                'js' => [
                    'profile.js',
                ],
                'csscdn' => [
                    'https://fonts.googleapis.com/css?family=Lato:700&display=swap&subset=latin-ext',
                    'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.min.css',
                ],
                'jscdn' => [
                    'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.min.js',
                ],
                'css' => [
                    'vox-profile-fix.css',
                    'vox-profile.css',
                ],
            ];

            $path = explode('/', request()->path())[2];
            if ($path == 'vox-iframe') {
                $params['skipSSO'] = true;
            }

            return $this->ShowVoxView('profile-vox', $params);
        }
        
        return null;
    }
}