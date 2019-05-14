<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use Response;
use Request;
use Auth;

class SSOController extends BaseController
{
	protected function manageCustomCookie() {
        if(!empty(request('slug')) && !empty(request('type')) && !empty(request('token'))) {
            //logging
	        $slug = $this->decrypt(request('slug'));
            $user = User::find( $slug );

            if($user) {
            	$token = $this->decrypt(request('token'));
	            $type = $this->decrypt(request('type'));
                $approved_statuses = array('approved', 'pending', 'test');
                if($user->self_deleted != NULL) {
                    return abort(404);
                } else if(!in_array($user->status, $approved_statuses)) {
                    return abort(404);
                } else {
                    $session_arr = [
                        'token' => $token,
                        'id' => $slug,
                        'type' => $type
                    ];
                    session(['logged_user' => $session_arr]);
                    Auth::login($user, true);

                    return Response::json(['success' => true]);
                }
            } else {
                return abort(404);
            }
        } else if(!empty(request('logout-token'))) {
            //logging out
            $token = $this->decrypt(request('logout-token'));
            if(!empty(session('logged_user')['token']) && session('logged_user')['token'] == $token) {
                session([
                    'logged_user' => false
                ]);
            }

            //TRP / Vox
	        if( Auth::guard('web')->user() ) {
	            Auth::guard('web')->user()->logoutActions();
	        }
            Auth::guard('web')->logout();
        } else {
            return abort(404);
        }
    }


    public function encrypt($raw_text) {
        $length = openssl_cipher_iv_length(env('CRYPTO_METHOD'));
        $iv = openssl_random_pseudo_bytes($length);
        $encrypted = openssl_encrypt($raw_text, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), OPENSSL_RAW_DATA, $iv);
        //here we append the $iv to the encrypted, because we will need it for the decryption
        $encrypted_with_iv = base64_encode($encrypted) . '|' . base64_encode($iv);
        return $encrypted_with_iv;
    }

    public function decrypt($encrypted_text) {
        list($data, $iv) = explode('|', $encrypted_text);
        $iv = base64_decode($iv);
        $raw_text = openssl_decrypt($data, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), 0, $iv);
        return $raw_text;
    }



}