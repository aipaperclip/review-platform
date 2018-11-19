<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserInvite;
use App\Models\UserTeam;
use App\Models\Country;
use App\Models\City;
use App\Models\Civic;
use Carbon\Carbon;

use Validator;
use Auth;
use Request;
use Response;
use Mail;
use Image;
use Illuminate\Support\Facades\Input;


class RegisterController extends FrontController
{
    public function register($locale=null) {

        if(!empty($this->user)) {
            return redirect(getLangUrl('profile'));
        }

		return $this->ShowView('register', [
			'js' => [
				'register.js'
			],
            'jscdn' => [
                'https://hosted-sip.civic.com/js/civic.sip.min.js',
            ],
            'csscdn' => [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ],
            'countries' => Country::get(),
            'categories' => $this->categories,
            'invitation_email' => session('invitation_email'),
            'invitation_name' => session('invitation_name'),
		]);
    }

    public function check_step_one() {

        $validator = Validator::make(Request::all(), [
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
        ]);

        if ($validator->fails()) {

            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {


            $is_blocked = User::checkBlocks( Request::input('name') , Request::input('email') );
            if( $is_blocked ) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'name' => $is_blocked
                    ]
                ] );
            }


            return Response::json( ['success' => true] );
        }

    }

    public function upload($locale=null) {

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            list($thumb, $full, $name) = User::addTempImage($img);
            return Response::json(['success' => true, 'thumb' => $thumb, 'name' => $name ]);
        }
    }


    public function invite_accept($locale=null, $id, $hash, $inv_id=null) {

        $user = User::find($id);

        if (!empty($user) && $user->canInvite('trp')) {

            if ($hash == $user->get_invite_token()) {

                if($this->user) {
                    if($this->user->id==$user->id) {
                        Request::session()->flash('success-message', trans('front.page.registration.invite-yourself'));
                    } else {
                        if(!$this->user->wasInvitedBy($user->id)) {
                            $inv = UserInvite::find($inv_id);
                            if(empty($inv)) {
                                $inv = UserInvite::where('user_id', $user->id)->where('invited_email', 'LIKE', $this->user->email)->first();
                            }
                            if(empty($inv)) {
                                $inv = new UserInvite;
                                $inv->user_id = $user->id;
                            }
                            $inv->invited_name = $this->user->name;
                            $inv->invited_email = $this->user->email;
                            $inv->invited_id = $this->user->id;
                            $inv->save();
                        }
                        Request::session()->flash('success-message', trans('front.page.registration.invitation-registered', [ 'name' => $user->name ]));
                    }
                    return redirect( $user->getLink() );
                } else {
                    $sess = [
                        'invited_by' => $user->id,
                    ];
                    $inv = UserInvite::find($inv_id);
                    if(!empty($inv)) {
                        $sess['invitation_name'] = $inv->invited_name;
                        $sess['invitation_email'] = $inv->invited_email;
                        $sess['invitation_id'] = $inv->id;
                    }
                    session($sess);

                    Request::session()->flash('success-message', trans('front.page.registration.invitation', [ 'name' => $user->name ]));
                    return redirect( getLangUrl('register'));                    
                }

            }
        } else {
            return redirect('/');
        }
    }

    public function register_form($locale=null) {

        $validator = Validator::make(Request::all(), [
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'country_id' => array('required', 'numeric'),
            'city_id' => array('required', 'numeric'),
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
            'country_id' => array('required', 'exists:countries,id'),
            'city_id' => array('required', 'exists:cities,id'),
            'zip' => array('required', 'string'),
            'address' =>  array('required', 'string'),
            'privacy' =>  array('required', 'accepted'),
            'photo' =>  array('required'),
        ]);

        if ($validator->fails()) {

            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $captcha = false;
            $cpost = [
                'secret' => env('CAPTCHA_SECRET'),
                'response' => Request::input('g-recaptcha-response'),
                'remoteip' => Request::ip()
            ];
            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            if($response) {
                $api_response = json_decode($response, true);
                if(!empty($api_response['success'])) {
                    $captcha = true;
                }
            }

            if( !$captcha ) {
                $ret = array(
                    'success' => false,
                    'messages' => array(
                        'captcha' => trans('front.page.registration.captcha')
                    )
                );

                return Response::json( $ret );
            }  

            $phone = null;
            $c = Country::find( Request::Input('country_id') );
            if( Request::input('type')=='clinic' ) {
                $phone = ltrim( str_replace(' ', '', Request::Input('phone')), '0');
                $pn = $c->phone_code.' '.$phone;

                $validator = Validator::make(['phone' => $pn], [
                    'phone' => ['required','phone:'.$c->code],
                ]);


                if ($validator->fails()) {
                    return Response::json( [
                        'success' => false, 
                        'messages' => [
                            'phone' => trans('front.page.registration.phone')
                        ]
                    ] );
                }
            }

            
            $newuser = new User;
            $newuser->name = Request::input('name');
            $newuser->email = Request::input('email');
            $newuser->country_id = Request::input('country_id');
            $newuser->city_id = Request::input('city_id');
            $newuser->password = bcrypt(Request::input('password'));
            $newuser->phone = $phone;
            $newuser->platform = 'trp';
            $newuser->zip = Request::input('zip');
            $newuser->address = Request::input('address');
            $newuser->website = Request::input('website');
            
            $newuser->gdpr_privacy = true;
            $newuser->is_dentist = 1;
            $newuser->is_clinic = Request::input('type')=='clinic' ? 1 : 0;

            if(!empty(session('invited_by'))) {
                $newuser->invited_by = session('invited_by');
            }

            $newuser->save();


            $clinic = User::find( Request::input('joinclinicid') );

            if(!empty($clinic)) {

                $newclinic = new UserTeam;
                $newclinic->dentist_id = $newuser->id;
                $newclinic->user_id = Request::input('joinclinicid');
                $newclinic->approved = 0;
                $newclinic->save();

                $clinic->sendTemplate(34, [
                    'dentist-name' =>$newuser->getName()
                ]);
            }


            if( Request::input('photo') ) {
                $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                $newuser->addImage($img);
            }
            

            $inv_id = session('invitation_id');
            if($inv_id) {
                $inv = UserInvite::find($inv_id);
                if($inv && $inv->user_id == session('invited_by')) {
                    $inv->invited_id = $newuser->id;
                    $inv->save();
                }
            }

            UserCategory::where('user_id', $newuser->id)->delete();
            if(!empty(Request::input('categories'))) {
                foreach (Request::input('categories') as $cat) {
                    $newc = new UserCategory;
                    $newc->user_id = $newuser->id;
                    $newc->category_id = $cat;
                    $newc->save();
                }
            }

            $newuser->sendTemplate( 1 );


            $mtext = 'New dentist/clinic registration:

            '.url('https://reviews.dentacoin.com/cms/users/edit/'.$newuser->id).'

            ';

            Mail::raw($mtext, function ($message) use ($newuser) {

                $receiver = 'ali.hashem@dentacoin.com';
                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');

                $message->from($sender, $sender_name);
                $message->to( $receiver );
                //$message->to( 'dokinator@gmail.com' );
                $message->replyTo($receiver, $newuser->getName());
                $message->subject('New Dentist/Clinic registration');
            });


            Auth::login($newuser, Request::input('remember'));

            // if($newuser->invited_by) {
            //     Request::session()->flash('success-message', trans('front.page.registration.success-by-invite', ['name' => $newuser->invitor->getName()]  ));
            // } else {
            //     Request::session()->flash('success-message', trans('front.page.registration.success'));
            // }
            Request::session()->flash('success-message', trans('front.page.registration.success-dentist'));
            return Response::json( [
                'success' => true,
                'url' => $newuser->invited_by ? $newuser->invitor->getLink() : getLangUrl('profile'),
            ] );

        }
    }

    public function forgot($locale=null) {

		return $this->ShowView('forgot-password');
    }

    public function forgot_form($locale=null) {

		$user = User::where([
            ['email','LIKE', Request::input('email') ]
        ])->first();

    	if(empty($user->id)) {
            Request::session()->flash('error-message', trans('front.page.registration.email-error'));
            return redirect( getLangUrl('forgot-password') );
        }

        $user->sendTemplate(5);

        Request::session()->flash('success-message', trans('front.page.registration.email-success'));
        return redirect( getLangUrl('forgot-password') );
    }

    public function recover($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                return $this->ShowView('recover-password', array(
                    'id' => $id,
                    'hash' => $hash,
                ));
            }
        }
        else {
            return redirect('');
        }
    }

    public function recover_form($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {
                $validator = Validator::make(Request::all(), [
                    'password' => array('required', 'min:6'),
                    'password-repeat' => 'required|same:password',
                ]);

                if ($validator->fails()) {
                    return redirect( getLangUrl('recover/'.$id.'/'.$hash))
                    ->withInput()
                    ->withErrors($validator);
                } else {
                    
                    $user->password = bcrypt(Request::input('password'));
                    $user->save();

                    Request::session()->flash('success-message', trans('front.page.recover.success'));
                    return redirect( getLangUrl('login') );
                }
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

                    $user = User::where( 'civic_id','LIKE', $data['userId'] )->withTrashed()->first();
                    if(empty($user) && $email) {
                        $user = User::where( 'email','LIKE', $email )->withTrashed()->first();            
                    }


                    if( $user ) {
                        if($user->deleted_at) {
                            $ret['message'] = 'You have been permanently banned and cannot return to the Review platform anymore.';
                        } else if( $user->isBanned('vox') ) {
                            $ret['message'] = trans('front.page.login.vox-ban');
                        } else {
                            Auth::login($user, true);
                            if(empty($user->civic_id)) {
                                $user->civic_id = $data['userId'];
                                $user->save();      
                            }

                            Request::session()->flash('success-message', trans('front.page.registration.have-account'));
                            $ret['success'] = true;
                            $ret['redirect'] = getLangUrl('profile');
                        }
                    } else {

                        $name = explode('@', $email)[0];


                        $is_blocked = User::checkBlocks( $name , $email );
                        if( $is_blocked ) {
                            return Response::json( [
                                'success' => false, 
                                'messages' => [
                                    'name' => $is_blocked
                                ]
                            ] );
                        }

                        $password = $name.date('WY');
                        $newuser = new User;
                        $newuser->name = $name;
                        $newuser->email = $email ? $email : '';
                        $newuser->password = bcrypt($password);
                        $newuser->is_dentist = 0;
                        $newuser->is_clinic = 0;
                        $newuser->civic_id = $data['userId'];
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

                        if($newuser->invited_by && $newuser->invitor->canInvite('trp')) {
                            $inv_id = session('invitation_id');
                            if($inv_id) {
                                $inv = UserInvite::find($inv_id);
                            } else {
                                $inv = new UserInvite;
                                $inv->user_id = $newuser->invited_by;
                                $inv->invited_email = $newuser->email;
                                $inv->invited_name = $newuser->name;
                                $inv->save();
                            }

                            $inv->invited_id = $newuser->id;
                            $inv->save();

                            $newuser->invitor->sendTemplate( $newuser->invitor->is_dentist ? 18 : 19, [
                                'who_joined_name' => $newuser->getName()
                            ] );
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
                            $newuser->sendTemplate( $newuser->is_dentist ? 3 : 4 );                
                        }

                        Auth::login($newuser, true);

                        if($newuser->invited_by && $newuser->invitor->is_dentist) {
                            Request::session()->flash('success-message', trans('front.page.registration.completed-by-invite', ['name' => $newuser->invitor->getName()]));
                        } else {
                            Request::session()->flash('success-message', trans('front.page.registration.completed'));
                        }
                        $ret['success'] = true;
                        $ret['redirect'] = $newuser->invited_by && $newuser->invitor->is_dentist ? $newuser->invitor->getLink() : getLangUrl('profile');
                    }
                    
                }

            } else {
                $ret['weak'] = true;
            }
        }

        
        return Response::json( $ret );
    }
}