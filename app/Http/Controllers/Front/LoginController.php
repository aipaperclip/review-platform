<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\Dcn;
use App\Models\Country;
use App\Models\Civic;
use App\Models\UserInvite;
use Carbon\Carbon;

use Socialite;
use Auth;
use Response;
use Request;
use Image;
use Mail;

class LoginController extends FrontController
{
    public function facebook_login($locale=null) {
    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return Socialite::driver('facebook')->redirect();
    }

    public function twitter_login($locale=null) {

    	config(['services.twitter.redirect' => getLangUrl('login/callback/twitter')]);
        return Socialite::driver('twitter')->redirect();
    }

    public function gplus_login($locale=null) {

    	config(['services.google.redirect' => getLangUrl('login/callback/gplus')]);
        return Socialite::driver('google')->redirect();
    }


    public function facebook_callback() {
        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('login'));
        }
    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return $this->try_social_login(Socialite::driver('facebook')->user());
    }

    public function twitter_callback() {
    	config(['services.twitter.redirect' => getLangUrl('login/callback/twitter')]);
        return $this->try_social_login(Socialite::driver('twitter')->user());
    }

    public function gplus_callback() {
    	config(['services.google.redirect' => getLangUrl('login/callback/gplus')]);
        return $this->try_social_login(Socialite::driver('google')->user());
    }


    private function try_social_login($s_user) {

        if( session('new_auth') && !empty($this->user) && empty($this->user->fb_id) && empty($this->user->civic_id) ) {
            $user = $this->user;

            $duplicate = User::where('fb_id', $s_user->getId() )->first();

            if( $duplicate ) {
                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'banned-popup']))
                ->withInput();
            } else {

                if( $user->loggedFromBadIp() ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'suspended-popup']))
                    ->withInput();
                }

                $user->fb_id = $s_user->getId();
                $user->save();
                session(['new_auth' => null]);

                Request::session()->flash('success-message');
                return redirect('/');
            }

        } else {
            if($s_user->getId()) {
                $user = User::where( 'fb_id','LIKE', $s_user->getId() )->first();
            }
            if(empty($user) && $s_user->getEmail()) {
                $user = User::where( 'email','LIKE', $s_user->getEmail() )->first(); //->where('id', '<', 5200)
                if( !empty($user) && $user->fb_id != $s_user->getId() ) {
                    $user->fb_id = $s_user->getId();
                    $user->save();
                }
            }

            if ($user) {
                if( $user->isBanned('trp') ) {
                    
                    return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
                }

                if( $user->loggedFromBadIp() ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'suspended-popup']))
                    ->withInput();
                }


                if(!empty(session('invitation_id'))) {

                    $inv_id = session('invitation_id');
                    if($inv_id) {
                        $inv = UserInvite::find($inv_id);

                        if ($inv && empty($inv->invited_id)) {
                            $inv->invited_id = $user->id;

                            if ($inv->invited_email == 'whatsapp') {
                                $inv->invited_email = $user->email;
                                $inv->invited_name = $user->name;
                            }
                            $inv->save();
                        }
                    }
                }


                $sess = [
                    'login_patient' => true,
                ];
                session($sess);

                Auth::login($user, true);

                $intended = session()->pull('intended-sess');

                return redirect( $intended ? $intended : getLangUrl('/'));
            } else {
                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-login']))
                    ->withInput()
                    ->with('error-message', trans('trp.popup.login.error-fb', [
                    'link' => '<a href="'.getLangUrl('register').'">',
                    'endlink' => '</a>',
                ]) );
            }
        }

    }




    public function facebook_register($locale=null, $type='patient') {
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);
        return Socialite::driver('facebook')->scopes(['user_location'])
        ->redirect();
    }
/*
    public function twitter_register($locale=null, $is_dentist) {
    	session(['is_dentist' => $is_dentist ]);
        config(['services.twitter.redirect' => getLangUrl('register/callback/twitter') ]);
        return Socialite::driver('twitter')->redirect();
    }

    public function gplus_register($locale=null, $is_dentist) {
    	session(['is_dentist' => $is_dentist ]);
        config(['services.google.redirect' => getLangUrl('register/callback/gplus') ]);
        return Socialite::driver('google')->redirect();
    }
*/

    public function facebook_callback_register() {
        
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('register') );
        }
        return $this->try_social_register(Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'location'])->user(), 'fb');
    }
/*
    public function twitter_callback_register() {
        config(['services.twitter.redirect' => getLangUrl('register/callback/twitter') ]);

        // if (!Request::has('code') || Request::has('denied')) {
        //     dd('bla');
        //     return redirect('register');
        // }
        return $this->try_social_register(Socialite::driver('twitter')->user(), 'tw');
    }

    public function gplus_callback_register() {
        config(['services.google.redirect' => getLangUrl('register/callback/gplus') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('register') );
        }
        return $this->try_social_register(Socialite::driver('google')->user(), 'gp');
    }
*/

    private function try_social_register($s_user, $network) {

        //dd($s_user);
        // return redirect( getLangUrl('register') )
        // ->withInput()
        // ->with('error-message', 'Due to the overwhelming surge in popularity, new registrations on Trusted Review Platform are currently disabled to allow for infrastructure & security upgrades. Thank you for your understanding!');

        $is_dentist = session('is_dentist');
        $is_clinic = session('is_clinic');
        if($s_user->getId()) {
            $user = User::where( 'fb_id','LIKE', $s_user->getId() )->withTrashed()->first();
        }
        if(empty($user) && $s_user->getEmail()) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->withTrashed()->first();            
        }

        if ($user) {

            if($user->deleted_at || $user->isBanned('trp')) {
                
                return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
            }


            if( $user->loggedFromBadIp() ) {
                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'suspended-popup']))
                ->withInput();
            }

            if(!empty(session('invitation_id'))) {

                $inv_id = session('invitation_id');
                $inv = UserInvite::find($inv_id);

                if ( !empty($inv) && empty($inv->invited_id)) {
                    $inv->invited_id = $user->id;
                    if ($inv->invited_email == 'whatsapp') {
                        $inv->invited_email = $user->email;
                        $inv->invited_name = $user->name;
                    }
                    $inv->save();
                }
            }
            
            Auth::login($user, true);
            if(empty($user->fb_id)) {
                $user->fb_id = $s_user->getId();
                $user->save();      
            }

            if(!empty(session('invitation_id'))) {
                $inv_id = session('invitation_id');
                $inv = UserInvite::find($inv_id);

                if(!empty($inv)) {
                    $dentist_invitor = User::find($inv->user_id);

                    if (!empty($dentist_invitor)) {
                        Request::session()->flash('success-message', trans('trp.popup.registration.have-account'));
                        return redirect($dentist_invitor->getLink().'?'. http_build_query(['popup'=>'submit-review-popup']));
                    }
                }

                Request::session()->flash('success-message', trans('trp.popup.registration.have-account'));
                return redirect(getLangUrl('/'));

            } else {
                Request::session()->flash('success-message', trans('trp.popup.registration.have-account'));
                return redirect(getLangUrl('/'));
            }

        } else {

            if (!empty($s_user->getEmail())) {

                $name = $s_user->getName() ? $s_user->getName() : ( !empty($s_user->user['first_name']) && !empty($s_user->user['last_name']) ? $s_user->user['first_name'].' '.$s_user->user['last_name'] : ( !empty($s_user->getEmail()) ? explode('@', $s_user->getEmail() )[0] : 'User' ) );


                $is_blocked = User::checkBlocks( $name , $s_user->getEmail() );
                if( $is_blocked ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']))
                    ->withInput()
                    ->with('error-message', $is_blocked );
                }

                if($s_user->getEmail() && (User::validateEmail($s_user->getEmail()) == true)) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']))
                    ->withInput()
                    ->with('error-message', nl2br(trans('trp.popup.login.existing_email')) );
                }


                $gender = !empty($s_user->user['gender']) ? ($s_user->user['gender']=='male' ? 'm' : 'f') : null;
                $birthyear = !empty($s_user->user['birthday']) ? explode('/', $s_user->user['birthday'])[2] : 0;

                if($birthyear && (intval(date('Y')) - $birthyear) < 18 ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']))
                    ->withInput()
                    ->with('error-message', nl2br(trans('trp.popup.login.over18')) );
                }

                $country_id = null;
                $state_name = null;
                $state_slug = null;
                $city_name = null;
                $lat = null;
                $lon = null;
                if (!empty($s_user->user['location']['name'])) {
                    $info = User::validateAddress( '', $s_user->user['location']['name'] );
                    if (!empty($info['country_name'])) {
                        $fb_country = $info['country_name'];
                        $country = Country::whereHas('translations', function ($query) use ($fb_country) {
                            $query->where('name', 'LIKE', $fb_country);
                        })->first();

                        if (!empty($country)) {
                            $country_id = $country->id;
                        }
                    }
                    if (!empty($info['state_name'])) {
                        $state_name = $info['state_name'];
                    }
                    if (!empty($info['state_slug'])) {
                        $state_slug = $info['state_slug'];
                    }
                    if (!empty($info['city_name'])) {
                        $city_name = $info['city_name'];
                    }
                    if (!empty($info['lat'])) {
                        $lat = $info['lat'];
                    }
                    if (!empty($info['lon'])) {
                        $lon = $info['lon'];
                    }
                }

                $password = $name.date('WY');
                $newuser = new User;
                $newuser->name = $name;
                $newuser->email = $s_user->getEmail() ? $s_user->getEmail() : '';
                $newuser->password = bcrypt($password);
                $newuser->country_id = !empty($country_id) ? $country_id : $this->country_id;
                $newuser->state_name = $state_name;
                $newuser->state_slug = $state_slug;
                $newuser->city_name = $city_name;
                $newuser->lat = $lat;
                $newuser->lon = $lon;
                $newuser->gender = $gender;
                $newuser->birthyear = !empty($birthyear) ? $birthyear : '';
                $newuser->fb_id = $s_user->getId();
                $newuser->gdpr_privacy = true;
                $newuser->platform = 'trp';
                $newuser->status = 'approved';
                
                if(!empty(session('invited_by'))) {
                    $newuser->invited_by = session('invited_by');
                }
                if(!empty(session('invite_secret'))) {
                    $newuser->invite_secret = session('invite_secret');
                }
                
                $newuser->save();

                $avatarurl = $s_user->getAvatar();
                if($network=='fb') {
                    $avatarurl .= '&width=600&height=600';                
                } else if($network=='gp') {
                    $avatarurl = str_replace('sz=50', 'sz=600', $avatarurl);
                } else if($network=='tw') {
                    $avatarurl = str_replace('_normal', '', $avatarurl);
                }
                if(!empty($avatarurl)) {
                    $img = Image::make($avatarurl);
                    $newuser->addImage($img);
                }


                if($newuser->invited_by && $newuser->invitor->canInvite('trp') && !empty(session('invitation_id'))) {
                    $inv_id = session('invitation_id');
                    $inv = UserInvite::find($inv_id);

                    if ($inv && empty($inv->invited_id)) {
                        $inv->invited_id = $newuser->id;

                        if ($inv->invited_email == 'whatsapp') {
                            $inv->invited_email = $newuser->email;
                            $inv->invited_name = $newuser->name;
                        }
                        $inv->save();
                        
                        // $newuser->invitor->sendTemplate( $newuser->invitor->is_dentist ? 18 : 19, [
                        //     'who_joined_name' => $newuser->getName()
                        // ] );
                    }
                }

                $sess = [
                    'invited_by' => null,
                    'invitation_name' => null,
                    'invitation_email' => null,
                    'invitation_id' => null,
                    'just_registered' => true,
                ];
                session($sess);

                if( $newuser->email ) {
                    $newuser->sendGridTemplate( 4 );
                }

                //
                //To be deleted
                //

                Auth::login($newuser, true);

                $want_to_invite = false;
                if(session('want_to_invite_dentist')) {
                    $want_to_invite = true;
                    session([
                        'want_to_invite_dentist' => null,
                    ]);
                }

                return redirect( $newuser->invited_by && $newuser->invitor->is_dentist ? $newuser->invitor->getLink().'?'.http_build_query(['popup'=>'submit-review-popup']) : getLangUrl('/').($want_to_invite ? '?'.http_build_query(['popup'=>'invite-new-dentist-popup']) : '' ) );
            } else {
                return redirect( getLanUrl('/').'?'. http_build_query(['popup'=>'popup-register']).'&error-message='.urlencode(trans('trp.popup.login.no-fb-email')));
            }
        }
    }

    public function civic() {
        $ret = [
            'success' => false
        ];

        $jwt = Request::input('jwtToken');
        $civic = Civic::where('jwtToken', 'LIKE', $jwt)->first();
        if(!empty($civic)) {
            $data = json_decode($civic->response, true);
            if(!empty($data['userId'])) {

                //dd($data);
                $email = null;
                $phone = null;

                if(!empty($data['data'])) {
                    foreach ($data['data'] as $dd) {
                        if($dd['label'] == 'contact.personal.email' && $dd['isOwner'] && $dd['isValid']) {
                            $email = $dd['value'];
                        }
                        if($dd['label'] == 'contact.personal.phoneNumber' && $dd['isOwner'] && $dd['isValid']) {
                            $phone = $dd['value'];
                        }
                    }
                }

                if(empty($email)) {
                    $ret['weak'] = true;
                } else {


                    if( session('new_auth') ) {
                        $user = $this->user;

                        $duplicate = User::where('civic_id', $data['userId'] )->first();

                        if( $duplicate ) {
                            $ret['message'] = 'There\'s another profile registered with this Civic Account';
                        } else {
                            $user->civic_id = $data['userId'];
                            $user->save();
                            session(['new_auth' => null]);

                            $ret['success'] = true;
                            $ret['redirect'] = getLangUrl('/');
                        }

                    } else {

                        $user = User::where( 'civic_id','LIKE', $data['userId'] )->first();
                        if(empty($user) && $email) {
                            $user = User::where( 'email','LIKE', $email )->first();            
                        }


                        if ($user) {
                            if( $user->isBanned('trp')) {
                                $ret['popup'] = 'banned-popup';
                            } else if( $user->loggedFromBadIp() ) {
                                $ret['popup'] = 'suspended-popup';
                            } else {

                                $sess = [
                                    'login_patient' => true,
                                ];
                                session($sess);
                                
                                Auth::login($user, true);
                                if(empty($user->civic_id)) {
                                    $user->civic_id = $data['userId'];
                                    $user->save();      
                                }

                                if(!empty(session('invitation_id'))) {

                                    $inv_id = session('invitation_id');
                                    if($inv_id) {
                                        $inv = UserInvite::find($inv_id);

                                        if ($inv && empty($inv->invited_id)) {
                                            $inv->invited_id = $user->id;

                                            if ($inv->invited_email == 'whatsapp') {
                                                $inv->invited_email = $user->email;
                                                $inv->invited_name = $user->name;
                                            }
                                            $inv->save();
                                        }
                                    }
                                }

                                $ret['success'] = true;
                                $ret['redirect'] = getLangUrl('/');
                            }
                        } else {
                            $ret['message'] = trans('trp.common.civic.not-found');
                        }

                    }
                }

            } else {
                $ret['weak'] = true;
            }
        }

        
        return Response::json( $ret );
    }
    

    public function status() {
        return !empty($this->user) ? $this->user->convertForResponse() : null;
    }
}